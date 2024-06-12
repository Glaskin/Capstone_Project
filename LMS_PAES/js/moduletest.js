document.addEventListener("DOMContentLoaded", () => {
  const steps = document.querySelectorAll(".step-content");
  const nextBtns = document.querySelectorAll(".next-btn");
  const prevBtns = document.querySelectorAll(".prev-btn");
  const finishBtn = document.querySelector(".finish-btn");

  let currentStep = 0;

  const showStep = step => {
    steps.forEach((stepContent, index) => {
      stepContent.classList.toggle("d-none", index !== step);
    });
  };

  nextBtns.forEach((btn, index) => {
    btn.addEventListener("click", () => {
      if (index < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
      }
    });
  });

  prevBtns.forEach((btn, index) => {
    btn.addEventListener("click", () => {
      if (index > 0) {
        currentStep--;
        showStep(currentStep);
      }
    });
  });

  finishBtn.addEventListener("click", () => {
    alert("Congratulations! You have completed the training module.");
    // Here you can redirect to another page or perform other actions
    window.location.href = 'Dashboard.html';
  });

  // Initialize the first step
  showStep(currentStep);
});
