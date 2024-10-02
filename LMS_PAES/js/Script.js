const courseContainer = document.getElementById('course-container');
const addBtn = document.getElementById('add-btn');
const customPromptModal = document.getElementById('custom-prompt-modal');
const courseTitleInput = document.getElementById('course-title-input');
const addCourseBtn = document.getElementById('add-course-btn');

addBtn.addEventListener('click', () => {
  customPromptModal.style.display = 'block';
});

addCourseBtn.addEventListener('click', () => {
  const title = courseTitleInput.value.trim();

  if (title !== '') {
    const newCourseCard = `
      <div class="col-md-4 mb-4">
        <div class="card">
          <img src="img/course-image-1.jpg" class="card-img-top" alt="Course Image" height="236">
          <div class="card-body">
            <h5 class="card-title">${title}</h5>
            <p class="card-text">Short description of the course.</p>
            <a href="Course.html" class="btn btn-primary">Start</a>
          </div>
        </div>
      </div>
    `;

    courseContainer.innerHTML += newCourseCard;
    customPromptModal.style.display = 'none';
    courseTitleInput.value = '';
  }
});

document.addEventListener('click', (e) => {
  if (e.target === customPromptModal) {
    customPromptModal.style.display = 'none';
  }
});