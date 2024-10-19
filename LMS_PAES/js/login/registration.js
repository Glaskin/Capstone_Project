// JavaScript code to handle student registration functionality

document.addEventListener("DOMContentLoaded", function() {
  // Handle registration form submission
  const registrationForm = document.getElementById("registrationForm");

  if (registrationForm) {
    registrationForm.addEventListener("submit", function(event) {
      event.preventDefault();

      // Get form input values
      const studentName = document.getElementById("studentName").value;
      const studentEmail = document.getElementById("studentEmail").value;
      const studentPassword = document.getElementById("studentPassword").value;
      const confirmPassword = document.getElementById("confirmPassword").value;

      // Basic validation
      if (studentPassword !== confirmPassword) {
        alert("Passwords do not match!");
        return;
      }

      if (!studentName || !studentEmail || !studentPassword) {
        alert("Please fill in all fields.");
        return;
      }

      // Create a new student object
      const newStudent = {
        name: studentName,
        email: studentEmail,
        password: studentPassword
      };

      // Simulate registration (e.g., save to database or local storage)
      console.log("Student registered:", newStudent);
      alert("Registration successful!");

      // Clear form fields
      registrationForm.reset();
    });
  }
});
