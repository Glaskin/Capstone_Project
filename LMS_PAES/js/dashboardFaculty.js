// JavaScript code to manage the faculty dashboard functionalities

// Functions for Dynamic Content
document.addEventListener('DOMContentLoaded', function () {
  // Sample faculty data (initially empty to remove pre-existing courses)
  const faculty = {
    name: 'Dr. Jane Smith',
    courses: [], // Removed existing courses
    studentsProgress: [
      {
        name: 'John Doe',
        course: 'Web Development',
        progress: 70
      },
      {
        name: 'Alice Johnson',
        course: 'Data Structures',
        progress: 80
      },
      {
        name: 'Mark Brown',
        course: 'Database Management',
        progress: 60
      }
    ]
  }

  // Set faculty name
  document.getElementById('userName').textContent = faculty.name

  // Populate courses list (initially empty)
  const coursesList = document.getElementById('coursesList')

  // Populate students progress
  const studentsProgress = document.getElementById('studentsProgress')
  faculty.studentsProgress.forEach(student => {
    const progressCard = document.createElement('div')
    progressCard.className = 'col-md-4 mb-4'
    progressCard.innerHTML = `
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">${student.name}</h5>
                <p class="card-text">Course: ${student.course}</p>
                <div class="progress mb-3">
                  <div class="progress-bar" role="progressbar" style="width: ${student.progress}%" aria-valuenow="${student.progress}" aria-valuemin="0" aria-valuemax="100">${student.progress}% Complete</div>
                </div>
              </div>
            </div>
          `
    studentsProgress.appendChild(progressCard)
  })

  // Handle create announcement form submission
  document
    .getElementById('announcementForm')
    .addEventListener('submit', function (event) {
      event.preventDefault()
      const title = document.getElementById('announcementTitle').value
      const date = document.getElementById('announcementDate').value
      const type = document.getElementById('announcementType').value

      const announcementBoard = document.getElementById('announcementBoard')
      const listItem = document.createElement('li')
      listItem.className =
        'list-group-item d-flex justify-content-between align-items-center'
      listItem.innerHTML = `
            ${title}
            <span class="badge bg-${type} rounded-pill">Date: ${date}</span>
          `
      announcementBoard.appendChild(listItem)
    })

  // Add Course Functionality
  const addCourseBtn = document.getElementById('add-course-btn');
  const courseContainer = document.getElementById('coursesList');

  if (addCourseBtn && courseContainer) {
    addCourseBtn.addEventListener('click', () => {
      const courseName = prompt('Enter the course name:');
      if (courseName) {
        const courseElement = document.createElement('div');
        courseElement.className = 'col-md-4 mb-4';
        courseElement.innerHTML = `
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">${courseName}</h5>
                    <p class="card-text">Number of Students: 0</p>
                    <a href="#" class="btn btn-primary">Manage Course</a>
                    <button class="btn btn-danger remove-course-btn mt-2">Remove Course</button>
                  </div>
                </div>
              `;
        courseContainer.appendChild(courseElement);

        // Add event listener for the remove button
        const removeBtn = courseElement.querySelector('.remove-course-btn');
        removeBtn.addEventListener('click', () => {
          courseContainer.removeChild(courseElement);
        });
      }
    });
  }
});

