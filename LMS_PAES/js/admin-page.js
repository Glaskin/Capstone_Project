// Mock Data for Users and Courses
let users = [
    {id: 1, username: "john_doe", email: "john@example.com"},
    {id: 2, username: "jane_doe", email: "jane@example.com"}
];

let courses = [
    {id: 1, name: "Cloud Basics", description: "Introduction to Cloud Concepts"},
    {id: 2, name: "JavaScript 101", description: "Learn the basics of JavaScript"}
];

// Load initial data 
window.onload = function() {
    loadUsers();
    loadCourses();
};

// Function to load users
function loadUsers() {
    let usersTable = document.getElementById("users-table");
    usersTable.innerHTML = ''; // Clear previous data
    users.forEach(user => {
        let row = `<tr>
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td><button class="btn btn-primary" onclick="editUser(${user.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteUser(${user.id})">Delete</button></td>
        </tr>`;
        usersTable.innerHTML += row;
    });
}

// Function to load courses
function loadCourses() {
    let coursesTable = document.getElementById("courses-table");
    coursesTable.innerHTML = ''; // Clear previous data
    courses.forEach(course => {
        let row = `<tr>
            <td>${course.id}</td>
            <td>${course.name}</td>
            <td>${course.description}</td>
            <td><button class="btn btn-primary" onclick="editCourse(${course.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteCourse(${course.id})">Delete</button></td>
        </tr>`;
        coursesTable.innerHTML += row;
    });
}

// Function to add a user (mock)
function addUser() {
    let username = prompt("Enter username:");
    let email = prompt("Enter email:");
    let newUser = {id: users.length + 1, username: username, email: email};
    users.push(newUser);
    loadUsers();
}

// Function to edit a user (mock)
function editUser(userId) {
    let user = users.find(u => u.id === userId);
    let newUsername = prompt("Edit username:", user.username);
    let newEmail = prompt("Edit email:", user.email);
    user.username = newUsername;
    user.email = newEmail;
    loadUsers();
}

// Function to delete a user
function deleteUser(userId) {
    users = users.filter(u => u.id !== userId);
    loadUsers();
}

// Function to add a course (mock)
function addCourse() {
    let courseName = prompt("Enter course name:");
    let description = prompt("Enter course description:");
    let newCourse = {id: courses.length + 1, name: courseName, description: description};
    courses.push(newCourse);
    loadCourses();
}

// Function to edit a course (mock)
function editCourse(courseId) {
    let course = courses.find(c => c.id === courseId);
    let newName = prompt("Edit course name:", course.name);
    let newDescription = prompt("Edit description:", course.description);
    course.name = newName;
    course.description = newDescription;
    loadCourses();
}

// Function to delete a course
function deleteCourse(courseId) {
    courses = courses.filter(c => c.id !== courseId);
    loadCourses();
}

// Announcement
let announcementId = 1; // To keep track of announcement IDs

function addAnnouncement() {
    const title = document.getElementById('announcementTitle').value;
    const content = document.getElementById('announcementContent').value;

    if (title && content) {
        const table = document.getElementById('announcements-table');
        const row = table.insertRow();
        row.innerHTML = `
            <td>${ announcementId}</td>
            <td>${title}</td>
            <td>${content}</td>
            <td>
                <button class="btn btn-danger" onclick="deleteAnnouncement(${announcementId})">Delete</button>
            </td>
        `;

        announcementId++;
        document.getElementById('announcementTitle').value = '';
        document.getElementById('announcementContent').value = '';
    }
}

function deleteAnnouncement(id) {
    const table = document.getElementById('announcements-table');
    for (let i = 0; i < table.rows.length; i++) {
        if (table.rows[i].cells[0].textContent === id.toString()) {
            table.deleteRow(i);
            break;
        }
    }
}

// Sample audit log data (replace with actual data fetching logic)
const auditLogData = [
    { timestamp: ' 2023-02-20 14:30:00', user: 'admin', action: 'Added new user', details: 'User ID: 12345' },
    { timestamp: '2023-02-19 10:45:00', user: 'moderator', action: 'Updated course', details: 'Course ID: 67890' },
    // ...
];

// Function to filter audit log by date
function filterAuditLog() {
    const dateFilter = document.getElementById('dateFilter').value;
    const filteredData = auditLogData.filter((entry) => entry.timestamp.includes(dateFilter));
    renderAuditLog(filteredData);
}

// Function to render audit log table
function renderAuditLog(data) {
    const tableBody = document.getElementById('auditlog-table');
    tableBody.innerHTML = '';
    data.forEach((entry) => {
        const tableRow = document.createElement('tr');
        tableRow.innerHTML = `
            <td>${entry.timestamp}</td>
            <td>${entry.user}</td>
            <td>${entry.action}</td>
            <td>${entry.details}</td>
        `;
        tableBody.appendChild(tableRow);
    });
}

// Initial render of audit log
renderAuditLog(auditLogData);

