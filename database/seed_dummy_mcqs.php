<?php
/**
 * MedMock FCPS Part-II Dummy MCQ Seeder
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();

$dummyMcqs = [
    // Cardiology
    [
        'q' => 'A 45-year-old male presents with sudden onset crushing substernal chest pain radiating to the left jaw. ECG shows ST-segment elevation in leads V1 to V4. Which coronary artery is most likely occluded?',
        'a' => 'Left Anterior Descending (LAD) Artery',
        'b' => 'Right Coronary Artery (RCA)',
        'c' => 'Left Circumflex (LCx) Artery',
        'd' => 'Left Main Coronary Artery',
        'e' => 'Posterior Descending Artery',
        'correct' => 'A',
        'explanation' => 'Leads V1-V4 view the anterior wall of the left ventricle, supplied by the Left Anterior Descending (LAD) coronary artery.',
        'subject' => 'Cardiology'
    ],
    [
        'q' => 'A 65-year-old female with hypertension presents with dyspnea, orthopnea, and bilateral lower limb edema. Echocardiogram shows ejection fraction of 30%. Which drug has been proven to reduce mortality in heart failure with reduced ejection fraction (HFrEF)?',
        'a' => 'Sacubitril/Valsartan (ARNI)',
        'b' => 'Furosemide',
        'c' => 'Digoxin',
        'd' => 'Amlodipine',
        'e' => 'Isosorbide Mononitrate',
        'correct' => 'A',
        'explanation' => 'ARNI (Sacubitril/Valsartan), Beta-blockers, MRA (Spironolactone/Eplerenone), and SGLT2 inhibitors reduce mortality in HFrEF. Loop diuretics like Furosemide improve symptoms but do not reduce overall mortality.',
        'subject' => 'Cardiology'
    ],
    [
        'q' => 'A 50-year-old male presents with fatigue and a harsh crescendo-decrescendo systolic murmur at the right upper sternal border radiating to the carotids. Which of the following classic triad symptoms indicates severe aortic stenosis?',
        'a' => 'Angina, Syncope, and Exertional Dyspnea',
        'b' => 'Fever, Weight loss, and Night sweats',
        'c' => 'Hemoptysis, Chest pain, and Cough',
        'd' => 'Jaundice, Ascites, and Splenomegaly',
        'e' => 'Headache, Palpitations, and Diaphoresis',
        'correct' => 'A',
        'explanation' => 'The classic symptom triad of severe aortic stenosis is Angina, Syncope, and Dyspnea ("S-A-D"). Average survival drops significantly once these symptoms develop.',
        'subject' => 'Cardiology'
    ],
    [
        'q' => 'A 24-year-old athlete collapses during a football match. Autopsy reveals asymmetrical septal hypertrophy and myofibrillar disarray. What is the most common genetic mutation associated with this condition?',
        'a' => 'Beta-myosin heavy chain (MYH7) or Myosin-binding protein C (MYBPC3)',
        'b' => 'Fibrillin-1 (FBN1)',
        'c' => 'Dystrophin (DMD)',
        'd' => 'Transthyretin (TTR)',
        'e' => 'SCN5A sodium channel gene',
        'correct' => 'A',
        'explanation' => 'Hypertrophic Cardiomyopathy (HCM) is an autosomal dominant condition most commonly caused by mutations in sarcomeric protein genes MYH7 and MYBPC3.',
        'subject' => 'Cardiology'
    ],
    [
        'q' => 'A 72-year-old female presents with syncope. ECG shows complete dissociation between P waves (rate 80/min) and QRS complexes (rate 35/min). What is the definitive long-term management?',
        'a' => 'Permanent Pacemaker Implantation',
        'b' => 'Intravenous Atropine',
        'c' => 'Oral Isoproterenol',
        'd' => 'Subcutaneous Low Molecular Weight Heparin',
        'e' => 'DC Cardioversion',
        'correct' => 'A',
        'explanation' => 'Third-degree (complete) AV block requires permanent cardiac pacemaker insertion to prevent Stokes-Adams attacks and sudden cardiac death.',
        'subject' => 'Cardiology'
    ],

    // Endocrinology
    [
        'q' => 'A 28-year-old female presents with heat intolerance, weight loss, palpitation, and exophthalmos. TSH is < 0.01 mIU/L and Free T4 is elevated. Which autoantibody is diagnostic for Graves\' disease?',
        'a' => 'TSH Receptor Autoantibodies (TRAb)',
        'b' => 'Anti-Thyroid Peroxidase (TPO) Antibodies',
        'c' => 'Anti-Thyroglobulin Antibodies',
        'd' => 'Anti-Nuclear Antibodies (ANA)',
        'e' => 'Anti-Smooth Muscle Antibodies',
        'correct' => 'A',
        'explanation' => 'TSH Receptor Autoantibodies (TRAb) stimulate thyroid hormone production and cause Graves\' disease and exophthalmos.',
        'subject' => 'Endocrinology'
    ],
    [
        'q' => 'A 42-year-old female presents with central obesity, purple abdominal striae, hypertension, and proximal muscle weakness. High-dose Dexamethasone suppression test causes >50% suppression of morning cortisol. What is the cause?',
        'a' => 'ACTH-secreting Pituitary Adenoma (Cushing\'s Disease)',
        'b' => 'Adrenal Cortical Adenoma',
        'c' => 'Ectopic ACTH production by Small Cell Lung Cancer',
        'd' => 'Adrenal Cortical Carcinoma',
        'e' => 'Exogenous corticosteroid use',
        'correct' => 'A',
        'explanation' => 'High-dose dexamethasone suppresses ACTH and cortisol in pituitary adenomas (Cushing\'s disease), whereas adrenal tumors and ectopic ACTH syndrome fail to suppress.',
        'subject' => 'Endocrinology'
    ],
    [
        'q' => 'A 20-year-old male with Type 1 Diabetes Mellitus presents with nausea, vomiting, abdominal pain, and Kussmaul respirations. Blood glucose is 420 mg/dL, arterial pH is 7.15, and serum ketones are positive. What is the first priority in fluid management?',
        'a' => '0.9% Normal Saline IV bolus (1000 mL/hr)',
        'b' => '5% Dextrose in Water (D5W)',
        'c' => 'Subcutaneous Regular Insulin',
        'd' => 'Sodium Bicarbonate IV infusion',
        'e' => 'Potassium Chloride IV push',
        'correct' => 'A',
        'explanation' => 'The initial priority in Diabetic Ketoacidosis (DKA) resuscitation is fluid restoration using isotonic 0.9% Normal Saline before starting insulin.',
        'subject' => 'Endocrinology'
    ],
    [
        'q' => 'A 35-year-old male with refractory hypertension is found to have serum potassium of 2.8 mEq/L. Plasma aldosterone to renin ratio (ARR) is > 30. What is the most likely diagnosis?',
        'a' => 'Primary Aldosteronism (Conn Syndrome)',
        'b' => 'Renal Artery Stenosis',
        'c' => 'Pheochromocytoma',
        'd' => 'Addison\'s Disease',
        'e' => 'Cushing\'s Syndrome',
        'correct' => 'A',
        'explanation' => 'An elevated plasma aldosterone-to-renin ratio (ARR > 30) with suppressed plasma renin activity is diagnostic for primary hyperaldosteronism (Conn syndrome).',
        'subject' => 'Endocrinology'
    ],

    // Gastroenterology & Hepatology
    [
        'q' => 'A 55-year-old male with long-standing history of liver cirrhosis presents with altered mental status, asterixis, and sleep reversal. Blood ammonia is elevated. What is the first-line oral agent used to reduce serum ammonia absorption?',
        'a' => 'Lactulose',
        'b' => 'Rifaximin',
        'c' => 'Neomycin',
        'd' => 'L-Ornithine L-Aspartate (LOLA)',
        'e' => 'Spironolactone',
        'correct' => 'A',
        'explanation' => 'Lactulose is non-absorbable disaccharide converted by colonic bacteria into organic acids, converting NH3 to non-absorbable NH4+ and acting as osmotic laxative.',
        'subject' => 'Gastroenterology'
    ],
    [
        'q' => 'A 32-year-old female presents with chronic bloody diarrhea, tenesmus, and lower abdominal cramping. Colonoscopy shows continuous mucosal inflammation extending from rectum to cecum. Biopsy shows crypt abscesses. What is the diagnosis?',
        'a' => 'Ulcerative Colitis',
        'b' => 'Crohn\'s Disease',
        'c' => 'Ischemic Colitis',
        'd' => 'Microscopic Colitis',
        'e' => 'Amebic Dysentery',
        'correct' => 'A',
        'explanation' => 'Ulcerative colitis causes continuous mucosal inflammation starting from the rectum with mucosal friability and crypt abscesses, unlike Crohn\'s skip lesions.',
        'subject' => 'Gastroenterology'
    ],
    [
        'q' => 'A 48-year-old female presents with pruritus, fatigue, and jaundice. Serum alkaline phosphatase (ALP) is 4x upper limit of normal. Anti-Mitochondrial Antibodies (AMA) are positive at high titer. What is the diagnosis?',
        'a' => 'Primary Biliary Cholangitis (PBC)',
        'b' => 'Primary Sclerosing Cholangitis (PSC)',
        'c' => 'Autoimmune Hepatitis',
        'd' => 'Wilson\'s Disease',
        'e' => 'Hemochromatosis',
        'correct' => 'A',
        'explanation' => 'Primary Biliary Cholangitis (PBC) is characterized by intrahepatic bile duct destruction, elevated ALP, and positive Anti-Mitochondrial Antibodies (AMA).',
        'subject' => 'Gastroenterology'
    ],
    [
        'q' => 'A 40-year-old male with chronic pancreatitis presents with steatorrhea, epigastric pain, and weight loss. What is the most effective therapy for pancreatic exocrine insufficiency?',
        'a' => 'Oral Pancreatic Enzyme Replacement Therapy (Creon)',
        'b' => 'Medium-chain Triglyceride (MCT) oil only',
        'c' => 'Proton Pump Inhibitors only',
        'd' => 'High fat diet',
        'e' => 'IV Somatostatin',
        'correct' => 'A',
        'explanation' => 'Pancreatic Enzyme Replacement Therapy (PERT) containing lipase, protease, and amylase taken with meals is essential to treat pancreatic exocrine insufficiency and steatorrhea.',
        'subject' => 'Gastroenterology'
    ],

    // Pulmonology
    [
        'q' => 'A 60-year-old chronic heavy smoker presents with progressive dyspnea and productive cough. Spirometry reveals FEV1/FVC ratio of 0.58 post-bronchodilator. Chest X-ray shows hyperinflated lungs and flattened diaphragms. What is the hallmark spirometric diagnostic criterion for COPD?',
        'a' => 'Post-bronchodilator FEV1/FVC ratio < 0.70',
        'b' => 'Pre-bronchodilator FEV1 < 80%',
        'c' => 'FVC < 50% predicted',
        'd' => 'DLCO > 120%',
        'e' => 'Reversibility of FEV1 > 15%',
        'correct' => 'A',
        'explanation' => 'COPD diagnosis requires post-bronchodilator FEV1/FVC < 0.70 establishing persistent airflow limitation.',
        'subject' => 'Pulmonology'
    ],
    [
        'q' => 'A 30-year-old male presents with fever, right-sided chest pain on deep inspiration, and rusty-colored sputum. Chest X-ray shows right lower lobe consolidation. What is the most common organism responsible for community-acquired pneumonia?',
        'a' => 'Streptococcus pneumoniae',
        'b' => 'Haemophilus influenzae',
        'c' => 'Mycoplasma pneumoniae',
        'd' => 'Legionella pneumophila',
        'e' => 'Klebsiella pneumoniae',
        'correct' => 'A',
        'explanation' => 'Streptococcus pneumoniae (pneumococcus) is the leading cause of typical bacterial community-acquired pneumonia.',
        'subject' => 'Pulmonology'
    ],
    [
        'q' => 'A 45-year-old female non-smoker presents with dyspnea, dry cough, and erythema nodosum on anterior shins. Chest X-ray reveals bilateral hilar lymphadenopathy (BHL). Serum ACE level is elevated. Non-caseating granulomas are seen on biopsy. What is the diagnosis?',
        'a' => 'Sarcoidosis',
        'b' => 'Tuberculosis',
        'c' => 'Histoplasmosis',
        'd' => 'Idiopathic Pulmonary Fibrosis',
        'e' => 'Wegener\'s Granulomatosis',
        'correct' => 'A',
        'explanation' => 'Sarcoidosis classically presents with bilateral hilar lymphadenopathy, erythema nodosum, elevated serum ACE, and non-caseating granulomas.',
        'subject' => 'Pulmonology'
    ],

    // Nephrology
    [
        'q' => 'A 4-year-old boy presents with facial puffiness, leg edema, and heavy proteinuria (4.5 g/24 hr). Serum albumin is 1.8 g/dL. Renal biopsy on light microscopy appears normal, but electron microscopy shows effacement of podocyte foot processes. What is the diagnosis?',
        'a' => 'Minimal Change Disease',
        'b' => 'Focal Segmental Glomerulosclerosis (FSGS)',
        'c' => 'Membranous Nephropathy',
        'd' => 'IgA Nephropathy',
        'e' => 'Post-Streptococcal Glomerulonephritis',
        'correct' => 'A',
        'explanation' => 'Minimal Change Disease is the most common cause of nephrotic syndrome in children, showing podocyte foot process effacement on EM.',
        'subject' => 'Nephrology'
    ],
    [
        'q' => 'A 65-year-old diabetic male develops severe hyperkalemia (Serum K+ 6.8 mEq/L) with ECG showing peaked T waves and QRS widening. What is the immediate intravenous medication administered to stabilize cardiac membranes?',
        'a' => 'Calcium Gluconate 10%',
        'b' => 'Regular Insulin with 50% Dextrose',
        'c' => 'Sodium Bicarbonate',
        'd' => 'Nebulized Salbutamol',
        'e' => 'Sodium Polystyrene Sulfonate (Kayexalate)',
        'correct' => 'A',
        'explanation' => 'IV Calcium Gluconate (or Calcium Chloride) stabilizes cardiac myocytes against hyperkalemia-induced arrhythmias immediately, though it does not lower serum potassium levels directly.',
        'subject' => 'Nephrology'
    ],
    [
        'q' => 'A 28-year-old male presents with recurrent episodes of gross hematuria 2 days following an upper respiratory tract infection ("synpharyngitic hematuria"). Serum IgA level is elevated. Biopsy shows mesangial IgA deposition. What is the diagnosis?',
        'a' => 'IgA Nephropathy (Berger\'s Disease)',
        'b' => 'Post-Streptococcal Glomerulonephritis',
        'c' => 'Goodpasture Syndrome',
        'd' => 'Alport Syndrome',
        'e' => 'Membranoproliferative Glomerulonephritis',
        'correct' => 'A',
        'explanation' => 'IgA Nephropathy typically presents with gross hematuria concurrently or 1-2 days after an upper respiratory infection (synpharyngitic), unlike PSGN which presents 1-3 weeks later.',
        'subject' => 'Nephrology'
    ],

    // Neurology
    [
        'q' => 'A 68-year-old male presents with resting tremor, rigidity, bradykinesia, and postural instability. Microscopic examination of substantia nigra reveals intracellular inclusion bodies called Lewy bodies. What protein is the primary component of Lewy bodies?',
        'a' => 'Alpha-synuclein',
        'b' => 'Tau protein',
        'c' => 'Amyloid-beta',
        'd' => 'Prion protein',
        'e' => 'TDP-43',
        'correct' => 'A',
        'explanation' => 'Lewy bodies are intracellular aggregations of misfolded alpha-synuclein proteins seen in Parkinson\'s disease and Lewy Body Dementia.',
        'subject' => 'Neurology'
    ],
    [
        'q' => 'A 25-year-old female presents with sudden onset weakness in right lower limb and optic neuritis in right eye. MRI brain shows periventricular demyelinating plaques. Lumbar puncture reveals CSF oligoclonal bands. What is the disease-modifying therapy for relapse prevention in Multiple Sclerosis?',
        'a' => 'Interferon Beta-1a / Glatiramer Acetate',
        'b' => 'High dose IV Methylprednisolone',
        'c' => 'Plasmapheresis',
        'd' => 'Intravenous Immunoglobulin (IVIG)',
        'e' => 'Pyridostigmine',
        'correct' => 'A',
        'explanation' => 'Interferon beta-1a, Glatiramer acetate, Natalizumab, and Ocrelizumab are disease-modifying therapies (DMTs) used for long-term relapse prevention in MS, whereas IV steroids treat acute relapses.',
        'subject' => 'Neurology'
    ],
    [
        'q' => 'A 30-year-old female presents with ptosis and diplopia that worsens towards the evening. Electromyography shows decremental response with repetitive nerve stimulation. Antibodies against which receptor are most commonly found?',
        'a' => 'Post-synaptic Acetylcholine Receptors (AChR)',
        'b' => 'Pre-synaptic Voltage-gated Calcium Channels (VGCC)',
        'c' => 'Muscle-specific Receptor Tyrosine Kinase (MuSK)',
        'd' => 'Aquaporin-4 (AQP4)',
        'e' => 'Voltage-gated Potassium Channels (VGKC)',
        'correct' => 'A',
        'explanation' => 'Myasthenia gravis is an autoimmune disorder caused by autoantibodies against post-synaptic acetylcholine receptors at the neuromuscular junction.',
        'subject' => 'Neurology'
    ],

    // Rheumatology & Hematology
    [
        'q' => 'A 35-year-old female presents with symmetrical pain and swelling in bilateral PIP and MCP joints of hands for 3 months with morning stiffness lasting > 1 hour. Anti-CCP antibodies are strongly positive. What disease-modifying anti-rheumatic drug (DMARD) is first-line therapy?',
        'a' => 'Methotrexate',
        'b' => 'Infliximab',
        'c' => 'Prednisolone',
        'd' => 'Colchicine',
        'e' => 'Allopurinol',
        'correct' => 'A',
        'explanation' => 'Methotrexate is the anchor first-line DMARD for Rheumatoid Arthritis.',
        'subject' => 'Rheumatology'
    ],
    [
        'q' => 'A 50-year-old male presents with sudden nocturnal onset severe pain, swelling, and redness in right first metatarsophalangeal joint (podagra). Arthrocentesis shows negatively birefringent needle-shaped crystals under polarized light. What is the diagnosis?',
        'a' => 'Gout (Monosodium Urate Crystals)',
        'b' => 'Pseudogout (Calcium Pyrophosphate Dihydrate)',
        'c' => 'Septic Arthritis',
        'd' => 'Osteoarthritis',
        'e' => 'Reactive Arthritis',
        'correct' => 'A',
        'explanation' => 'Monosodium urate crystals in gout are needle-shaped and negatively birefringent, while CPPD crystals in pseudogout are rhomboid and positively birefringent.',
        'subject' => 'Rheumatology'
    ],
    [
        'q' => 'A 65-year-old male presents with bone pain, fatigue, and recurrent infections. Serum protein electrophoresis shows an M-spike in the gamma-globulin region. Bone marrow biopsy reveals > 10% plasma cells. Skull X-ray shows punched-out lytic lesions. What is the diagnosis?',
        'a' => 'Multiple Myeloma',
        'b' => 'Hodgkin Lymphoma',
        'c' => 'Chronic Lymphocytic Leukemia (CLL)',
        'd' => 'Waldenstrom Macroglobulinemia',
        'e' => 'Burkitt Lymphoma',
        'correct' => 'A',
        'explanation' => 'Multiple Myeloma is a plasma cell neoplasm characterized by CRAB criteria: HyperCalcemia, Renal insufficiency, Anemia, and Bone lytic lesions.',
        'subject' => 'Hematology'
    ]
];

$stmt = $pdo->prepare("
    INSERT INTO mcqs (question, option_a, option_b, option_c, option_d, option_e, correct_option, explanation, subject)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$added = 0;

foreach ($dummyMcqs as $q) {
    $check = $pdo->prepare("SELECT id FROM mcqs WHERE question = ?");
    $check->execute([$q['q']]);

    if (!$check->fetch()) {
        $stmt->execute([
            $q['q'],
            $q['a'],
            $q['b'],
            $q['c'],
            $q['d'],
            $q['e'],
            $q['correct'],
            $q['explanation'],
            $q['subject']
        ]);
        $added++;
    }
}

echo "<h2>FCPS Part-II Questions Loaded</h2>";
echo "<p style='color:green;'>✓ Added <b>$added</b> FCPS Part-II medical MCQs into database!</p>";
$total = (int)$pdo->query("SELECT COUNT(*) FROM mcqs")->fetchColumn();
echo "<p>Total MCQs currently in Database Pool: <b>$total</b></p>";
