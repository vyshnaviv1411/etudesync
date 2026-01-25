let index = 0;

showQuestion();

flipBtn.onclick = () => showAnswer();
nextBtn.onclick = () => index++, showQuestion();