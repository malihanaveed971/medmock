<?php
/**
 * MedMock CSV & QBank Importer Helper
 * Supports both standard structured CSV files and QBank web/PDF dump exports
 * (Pastest, PassMedicine, etc.) with automatic block detection, noise filtering,
 * option extraction, correct answer resolution, and subject mapping.
 */

function parseQbankTextBlocks($filePath, $defaultSubject) {
    $content = file_get_contents($filePath);
    if ($content === false) return [];

    // Remove UTF-8 BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    $lines = explode("\n", $content);
    $blocks = [];
    $cur = null;

    foreach ($lines as $l) {
        $line = trim(trim($l), '"');
        if (preg_match('/Question\s+\d+\s+of\s+\d+/i', $line)) {
            if ($cur !== null) $blocks[] = $cur;
            $cur = [];
            continue;
        }
        if ($cur !== null) $cur[] = $line;
    }
    if ($cur !== null) $blocks[] = $cur;

    // Derive subject from filename
    $filenameBase = pathinfo($filePath, PATHINFO_FILENAME);
    // e.g. Pastes-_Part2_-Endocrinology -> Endocrinology
    $subjectName = ucwords(trim(str_replace(['Pastes', '_Part2_', 'Part2', 'Qbank', 'Unseen', 'Practice', '1', '_', '-'], ' ', $filenameBase)));
    $subjectName = preg_replace('/\s+/', ' ', $subjectName);
    if (empty($subjectName) || preg_match('/^(sample|test|mcqs|questions|read|me)$/i', $subjectName)) {
        $subjectName = $defaultSubject;
    }

    $parsedMCQs = [];
    $noiseRegex = '/^(LOG OUT|Difficulty:|Peer Responses|Show More Questions|Session Progress|Responses Correct:|Responses Incorrect:|Responses Total:|Responses - %|Expanded|Over view|Clinical Presentation|Differential Diagnosis|Diagnosis \/ Investigation|Management|Prognosis|Links to NICE|Your answer was|Next question|PassMedicine|Qbank|Medicalstudyzone|This PDF was|Furthermore You can|Rate this question|Tag Question|Feedback|End Session|Blog About|Pastest|Contact Us|Help|©|\x0C)/i';

    foreach ($blocks as $rawLines) {
        $questionLines = [];
        $options = ['A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => ''];
        $correctOption = '';
        $explanationLines = [];
        
        $state = 'question';
        $lastOption = '';

        foreach ($rawLines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || preg_match($noiseRegex, $trimmed)) {
                continue;
            }

            // Strip trailing right-side column text from web dump lines
            $trimmed = preg_replace('/\s{2,}(Difficulty:\s*\w+|Peer Responses.*|Responses Incorrect:.*|Responses Correct:.*|Responses Total:.*|Responses - %.*|Show More Questions.*|Over view|Clinical Presentation|Differential Diagnosis|Diagnosis \/ Investigation|Management|Prognosis|Links to NICE.*)$/i', '', $trimmed);
            $trimmed = trim($trimmed);
            if (empty($trimmed)) continue;

            if (preg_match('/^Explanation\b/i', $trimmed)) {
                $state = 'explanation';
                continue;
            }

            // Option lines match: "A    Text" or "A. Text" or "A) Text"
            if ($state !== 'explanation' && preg_match('/^([A-E])(?:[\t\s]{2,}|[\.\)])\s*(.+)/i', $trimmed, $m)) {
                $optLetter = strtoupper($m[1]);
                $optText = trim($m[2]);
                if (isset($options[$optLetter])) {
                    $options[$optLetter] = $optText;
                    $lastOption = $optLetter;
                    $state = 'options';
                    continue;
                }
            }

            if ($state === 'question') {
                $questionLines[] = $trimmed;
            } elseif ($state === 'options') {
                if (!empty($lastOption) && empty($options[$lastOption])) {
                    $options[$lastOption] = $trimmed;
                } elseif (!empty($lastOption)) {
                    $options[$lastOption] .= " " . $trimmed;
                }
            } elseif ($state === 'explanation') {
                if (empty($correctOption)) {
                    if (preg_match('/^([A-E])(?:[\t\s]{2,}|[\.\)])\s*(.+)/i', $trimmed, $m)) {
                        $correctOption = strtoupper($m[1]);
                        $explanationLines[] = $trimmed;
                        continue;
                    } elseif (preg_match('/^(Option|Answer|Correct[\s:]+)?([A-E])\b/i', $trimmed, $m)) {
                        if (in_array(strtoupper($m[2]), ['A','B','C','D','E'])) {
                            $correctOption = strtoupper($m[2]);
                        }
                    }
                }
                $explanationLines[] = $trimmed;
            }
        }

        // Fallback correct option resolution
        if (empty($correctOption)) {
            $expText = implode(' ', $explanationLines);
            if (preg_match('/\bOption\s+([A-E])\s+(is correct|is the correct|is answer)/i', $expText, $m)) {
                $correctOption = strtoupper($m[1]);
            } else {
                foreach (['A','B','C','D','E'] as $let) {
                    if (!empty($options[$let]) && strlen($options[$let]) > 4 && stripos($expText, $options[$let]) !== false) {
                        $correctOption = $let;
                        break;
                    }
                }
            }
        }
        if (empty($correctOption)) {
            $correctOption = 'A';
        }

        $qText = implode("\n", $questionLines);
        $expText = implode("\n", $explanationLines);

        if (!empty($qText) && (!empty($options['A']) || !empty($options['B']))) {
            $parsedMCQs[] = [
                'question'       => $qText,
                'option_a'       => $options['A'],
                'option_b'       => $options['B'],
                'option_c'       => $options['C'],
                'option_d'       => $options['D'],
                'option_e'       => $options['E'] !== '' ? $options['E'] : NULL,
                'correct_option' => $correctOption,
                'explanation'    => $expText,
                'subject'        => $subjectName
            ];
        }
    }

    return $parsedMCQs;
}

function importMcqCsv($filePath, PDO $conn, $defaultSubject = 'General Medicine') {
    @ini_set('auto_detect_line_endings', '1');
    @ini_set('memory_limit', '512M');
    @set_time_limit(300);

    if (!file_exists($filePath) || !is_readable($filePath)) {
        return [
            'success'  => false,
            'imported' => 0,
            'skipped'  => 0,
            'message'  => "File does not exist or is not readable: " . basename($filePath),
            'reasons'  => [],
            'debug'    => []
        ];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return [
            'success'  => false,
            'imported' => 0,
            'skipped'  => 0,
            'message'  => "Could not read file content.",
            'reasons'  => [],
            'debug'    => []
        ];
    }

    // Check if file is a QBank Text Block Export (PassMedicine, Pastest, etc.)
    if (preg_match('/Question\s+\d+\s+of\s+\d+/i', $content)) {
        $qbankMcqs = parseQbankTextBlocks($filePath, $defaultSubject);
        if (!empty($qbankMcqs)) {
            $stmt = $conn->prepare("
                INSERT INTO mcqs (question, option_a, option_b, option_c, option_d, option_e, correct_option, explanation, subject)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $imported = 0;
            foreach ($qbankMcqs as $mcq) {
                $stmt->execute([
                    $mcq['question'],
                    $mcq['option_a'],
                    $mcq['option_b'],
                    $mcq['option_c'],
                    $mcq['option_d'],
                    $mcq['option_e'],
                    $mcq['correct_option'],
                    $mcq['explanation'],
                    $mcq['subject']
                ]);
                $imported++;
            }
            return [
                'success'  => true,
                'imported' => $imported,
                'skipped'  => 0,
                'reasons'  => [],
                'debug'    => ['type' => 'QBank Block Parser', 'count' => count($qbankMcqs)]
            ];
        }
    }

    // Remove UTF-8 BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    $tmpFile = tempnam(sys_get_temp_dir(), 'csv_imp_');
    file_put_contents($tmpFile, $content);

    $handle = fopen($tmpFile, "r");
    if (!$handle) {
        @unlink($tmpFile);
        return [
            'success'  => false,
            'imported' => 0,
            'skipped'  => 0,
            'message'  => "Could not open temporary file for reading.",
            'reasons'  => [],
            'debug'    => []
        ];
    }

    // Delimiter trial: Test comma, semicolon, tab, pipe
    $possibleDelimiters = [',', ';', "\t", '|'];
    $delimiter = ',';
    $maxColsFound = 0;

    foreach ($possibleDelimiters as $testDelim) {
        rewind($handle);
        $testLine = fgetcsv($handle, 0, $testDelim);
        if ($testLine && is_array($testLine) && count($testLine) > $maxColsFound) {
            $maxColsFound = count($testLine);
            $delimiter = $testDelim;
        }
    }

    rewind($handle);
    $rawHeader = fgetcsv($handle, 0, $delimiter);

    // Build dynamic column map from header
    $colMap = [];
    if ($rawHeader && is_array($rawHeader)) {
        foreach ($rawHeader as $idx => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $colName)));
            
            if (in_array($cleanCol, [
                'question', 'q', 'questiontext', 'questionstatement', 'stem', 'prompt', 'mcq', 
                'title', 'questiontitle', 'item', 'statement', 'details', 'problem', 'description', 
                'questionname', 'qtext', 'questioncontent', 'mcqquestion'
            ])) {
                $colMap['question'] = $idx;
            } elseif (in_array($cleanCol, [
                'optiona', 'opta', 'a', 'choicea', 'ansa', 'option1', 'opt1', 'choice1', '1'
            ])) {
                $colMap['option_a'] = $idx;
            } elseif (in_array($cleanCol, [
                'optionb', 'optb', 'b', 'choiceb', 'ansb', 'option2', 'opt2', 'choice2', '2'
            ])) {
                $colMap['option_b'] = $idx;
            } elseif (in_array($cleanCol, [
                'optionc', 'optc', 'c', 'choicec', 'ansc', 'option3', 'opt3', 'choice3', '3'
            ])) {
                $colMap['option_c'] = $idx;
            } elseif (in_array($cleanCol, [
                'optiond', 'optd', 'd', 'choiced', 'ansd', 'option4', 'opt4', 'choice4', '4'
            ])) {
                $colMap['option_d'] = $idx;
            } elseif (in_array($cleanCol, [
                'optione', 'opte', 'e', 'choicee', 'anse', 'option5', 'opt5', 'choice5', '5'
            ])) {
                $colMap['option_e'] = $idx;
            } elseif (in_array($cleanCol, [
                'correctoption', 'correct', 'answer', 'ans', 'key', 'correctanswer', 'answerkey', 
                'rightoption', 'rightanswer', 'correctkey', 'solutionkey', 'anskey', 'keyanswer'
            ])) {
                $colMap['correct_option'] = $idx;
            } elseif (in_array($cleanCol, [
                'explanation', 'rationale', 'exp', 'solution', 'detail', 'details', 'reason', 
                'discussion', 'comment', 'comments', 'notes', 'feedback', 'exptext'
            ])) {
                $colMap['explanation'] = $idx;
            } elseif (in_array($cleanCol, [
                'subject', 'topic', 'category', 'specialty', 'speciality', 'section', 'domain', 
                'tag', 'tags', 'subtopic', 'system'
            ])) {
                $colMap['subject'] = $idx;
            }
        }
    }

    // Read sample first data row to test column positions
    $pos = ftell($handle);
    $sampleDataRow = fgetcsv($handle, 0, $delimiter);
    fseek($handle, $pos);

    // Smart Fallback if key columns weren't matched via header names
    if (!isset($colMap['question']) || !isset($colMap['option_a']) || !isset($colMap['correct_option'])) {
        $offset = 0;
        if ($sampleDataRow && is_array($sampleDataRow) && count($sampleDataRow) >= 4) {
            $col0 = trim($sampleDataRow[0]);
            if (preg_match('/^\d{1,8}$/', $col0) && strlen($sampleDataRow[1] ?? '') > 10) {
                $offset = 1;
            }
        }

        if (!isset($colMap['question']))       $colMap['question']       = 0 + $offset;
        if (!isset($colMap['option_a']))       $colMap['option_a']       = 1 + $offset;
        if (!isset($colMap['option_b']))       $colMap['option_b']       = 2 + $offset;
        if (!isset($colMap['option_c']))       $colMap['option_c']       = 3 + $offset;
        if (!isset($colMap['option_d']))       $colMap['option_d']       = 4 + $offset;
        if (!isset($colMap['option_e']))       $colMap['option_e']       = 5 + $offset;
        if (!isset($colMap['correct_option'])) $colMap['correct_option'] = 6 + $offset;
        if (!isset($colMap['explanation']))    $colMap['explanation']    = 7 + $offset;
        if (!isset($colMap['subject']))        $colMap['subject']        = 8 + $offset;

        // Check if header row was actually data
        if ($rawHeader && is_array($rawHeader) && count($rawHeader) >= 3) {
            $testCorrect = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $rawHeader[$colMap['correct_option']] ?? '')));
            if (in_array($testCorrect, ['A','B','C','D','E','1','2','3','4','5'])) {
                rewind($handle);
            }
        }
    }

    // Derive subject from filename as a fallback
    $filenameBase = pathinfo($filePath, PATHINFO_FILENAME);
    $filenameSubject = ucwords(trim(str_replace(['_', '-'], ' ', $filenameBase)));
    if (empty($filenameSubject) || preg_match('/^(sample|medmock_mcq_sample|test|mcqs|questions)$/i', $filenameSubject)) {
        $filenameSubject = $defaultSubject;
    }

    $stmt = $conn->prepare("
        INSERT INTO mcqs (question, option_a, option_b, option_c, option_d, option_e, correct_option, explanation, subject)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $imported = 0;
    $skipped = 0;
    $skippedReasons = [];

    $rowNum = 1;
    while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        $rowNum++;
        if (empty(array_filter($data, function($val) { return trim($val) !== ''; }))) {
            continue;
        }

        $getVal = function($key, $default = '') use ($colMap, $data) {
            if (isset($colMap[$key]) && isset($data[$colMap[$key]])) {
                return trim($data[$colMap[$key]]);
            }
            return $default;
        };

        $question       = $getVal('question');
        $option_a       = $getVal('option_a');
        $option_b       = $getVal('option_b');
        $option_c       = $getVal('option_c');
        $option_d       = $getVal('option_d');
        $option_e       = $getVal('option_e');
        $option_e       = ($option_e !== '') ? $option_e : NULL;
        $raw_correct    = $getVal('correct_option');
        $explanation    = $getVal('explanation');
        $subject        = $getVal('subject', '');
        
        if (empty($subject)) {
            $subject = !empty($filenameSubject) ? $filenameSubject : $defaultSubject;
        }
        $subject = ucwords(strtolower(trim($subject)));

        // Asterisk / Tagged option check
        $starredOption = '';
        $cleanOpt = function(&$optStr, $letter) use (&$starredOption) {
            if (empty($optStr)) return;
            if (strpos($optStr, '*') === 0 || preg_match('/\b(correct|ans|key)\b/i', $optStr)) {
                $starredOption = $letter;
                $optStr = trim(preg_replace('/^\*|\b\(?(correct|ans|key)\)?\b/i', '', $optStr));
            }
        };

        $cleanOpt($option_a, 'A');
        $cleanOpt($option_b, 'B');
        $cleanOpt($option_c, 'C');
        $cleanOpt($option_d, 'D');
        if ($option_e) $cleanOpt($option_e, 'E');

        // Flexible answer parsing
        $correct_option = '';
        $clean_correct = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $raw_correct)));

        if (!empty($starredOption)) {
            $correct_option = $starredOption;
        } elseif (in_array($clean_correct, ['A', 'B', 'C', 'D', 'E'])) {
            $correct_option = $clean_correct;
        } elseif (in_array($clean_correct, ['1', '2', '3', '4', '5'])) {
            $mapNum = ['1'=>'A', '2'=>'B', '3'=>'C', '4'=>'D', '5'=>'E'];
            $correct_option = $mapNum[$clean_correct];
        } else {
            if (!empty($raw_correct)) {
                if (strcasecmp($raw_correct, $option_a) === 0) $correct_option = 'A';
                elseif (strcasecmp($raw_correct, $option_b) === 0) $correct_option = 'B';
                elseif (strcasecmp($raw_correct, $option_c) === 0) $correct_option = 'C';
                elseif (strcasecmp($raw_correct, $option_d) === 0) $correct_option = 'D';
                elseif ($option_e !== null && strcasecmp($raw_correct, $option_e) === 0) $correct_option = 'E';
            }
            
            // Search all remaining cells in the row for a standalone A/B/C/D/E or 1-5 key
            if (empty($correct_option) && is_array($data)) {
                foreach ($data as $cellIdx => $cellVal) {
                    $cellClean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cellVal)));
                    if (in_array($cellClean, ['A', 'B', 'C', 'D', 'E'])) {
                        $correct_option = $cellClean;
                        break;
                    } elseif (in_array($cellClean, ['1', '2', '3', '4', '5'])) {
                        $mapNum = ['1'=>'A', '2'=>'B', '3'=>'C', '4'=>'D', '5'=>'E'];
                        $correct_option = $mapNum[$cellClean];
                        break;
                    }
                }
            }

            // Ultimate fallback: if question & options exist, default to A so question is preserved
            if (empty($correct_option) && !empty($question) && !empty($option_a) && !empty($option_b)) {
                $correct_option = 'A';
            }
        }

        if (!empty($question) && !empty($option_a) && !empty($option_b) && !empty($correct_option)) {
            $stmt->execute([
                $question, $option_a, $option_b, $option_c, $option_d, $option_e, $correct_option, $explanation, $subject
            ]);
            $imported++;
        } else {
            $skipped++;
            $reasons = [];
            if (empty($question)) $reasons[] = "missing question";
            if (empty($option_a) || empty($option_b)) $reasons[] = "missing options";
            if (empty($correct_option)) $reasons[] = "invalid correct option ('" . htmlspecialchars($raw_correct) . "')";
            if (count($skippedReasons) < 5) {
                $skippedReasons[] = "Row $rowNum: " . implode(", ", $reasons);
            }
        }
    }

    fclose($handle);
    @unlink($tmpFile);

    $debugInfo = [
        'delimiter' => ($delimiter === "\t" ? 'TAB (\t)' : ($delimiter === '|' ? 'PIPE (|)' : ($delimiter === ';' ? 'SEMICOLON (;)' : 'COMMA (,)'))),
        'header_count' => count($rawHeader ?? []),
        'headers' => $rawHeader,
        'sample_row' => $sampleDataRow,
        'col_map' => $colMap
    ];

    return [
        'success'  => true,
        'imported' => $imported,
        'skipped'  => $skipped,
        'reasons'  => $skippedReasons,
        'debug'    => $debugInfo
    ];
}
