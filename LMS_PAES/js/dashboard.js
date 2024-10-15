// Functions for Dynamic Content
document.addEventListener("DOMContentLoaded", function() {
  // Sample user data (to be replaced with data from a backend system)
  const user = {
    name: "John Doe",
    recentActivity:
      "You recently completed Module 3: Introduction to JavaScript.",
    nextLesson: "Start Module 4: Advanced JavaScript Techniques.",
    courses: [
      {
        title: "Course Title 1",
        description: "Short description of the course.",
        progress: 41,
        image: "img/course-image-1.jpg"
      },
      {
        title: "Course Title 2",
        description: "Short description of the course.",
        progress: 15,
        image: "img/course-image-2.jpg"
      },
      {
        title: "Course Title 3",
        description: "Short description of the course.",
        progress: 70,
        image: "img/course-image-3.jpg"
      }
    ],
    deadlines: [
      {
        title: "Assignment: JavaScript Basics",
        dueDate: "Oct 16",
        type: "danger"
      },
      {
        title: "Lab Activity: Network Technologies",
        dueDate: "Oct 18",
        type: "warning"
      },
      {
        title: "Quiz: Data Privacy Module",
        dueDate: "Oct 23",
        type: "primary"
      }
    ]
  };

  // Set user name
  document.getElementById("userName").textContent = user.name;

  // Set recent activity and next lesson
  document.getElementById("recentActivity").textContent = user.recentActivity;
  document.getElementById("nextLesson").textContent = user.nextLesson;

  // Populate course list
  const courseList = document.getElementById("courseList");
  user.courses.forEach(course => {
    const courseCard = document.createElement("div");
    courseCard.className = "col-md-4 mb-4";
    courseCard.innerHTML = `
      <div class="card">
        <img src="${course.image}" class="card-img-top" alt="Course Image" height="236">
        <div class="card-body">
          <h5 class="card-title">${course.title}</h5>
          <p class="card-text">${course.description}</p>
          <div class="progress mb-3">
            <div class="progress-bar" role="progressbar" style="width: ${course.progress}%" aria-valuenow="${course.progress}" aria-valuemin="0" aria-valuemax="100">${course.progress}% Complete</div>
          </div>
          <a href="Course.html" class="btn btn-primary">${course.progress === 0
            ? "Start"
            : "Continue"}</a>
        </div>
      </div>
    `;
    courseList.appendChild(courseCard);
  });

  // Populate upcoming deadlines
  const upcomingDeadlines = document.getElementById("upcomingDeadlines");
  user.deadlines.forEach(deadline => {
    const listItem = document.createElement("li");
    listItem.className =
      "list-group-item d-flex justify-content-between align-items-center";
    listItem.innerHTML = `
      ${deadline.title}
      <span class="badge bg-${deadline.type} rounded-pill">Due: ${deadline.dueDate}</span>
    `;
    upcomingDeadlines.appendChild(listItem);
  });
});
