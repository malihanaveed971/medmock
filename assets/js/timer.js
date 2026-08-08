document.addEventListener("DOMContentLoaded", function () {
    let timerElement = document.getElementById("timer");
    if (!timerElement) return;

    let seconds = parseInt(timerElement.getAttribute("data-seconds")) || 12000;

    function updateTimer() {
        if (seconds <= 0) {
            timerElement.innerHTML = "00:00:00";
            alert("Exam Time Completed! Submitting your test automatically...");
            window.location = "results.php?auto_submit=1";
            return;
        }

        let hours = Math.floor(seconds / 3600);
        let minutes = Math.floor((seconds % 3600) / 60);
        let sec = seconds % 60;

        let display = 
            (hours > 0 ? String(hours).padStart(2, '0') + ":" : "") +
            String(minutes).padStart(2, '0') + ":" +
            String(sec).padStart(2, '0');

        timerElement.innerHTML = display;
        seconds--;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
});