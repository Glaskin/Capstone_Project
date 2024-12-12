// Toggle visibility for the password field
document.getElementById('toggleStudentPassword').addEventListener('click', function () {
  const passwordInput = document.getElementById('studentPassword');
  const toggleIcon = document.getElementById('toggleStudentIcon');

  if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('bi-eye');
      toggleIcon.classList.add('bi-eye-slash');
  } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('bi-eye-slash');
      toggleIcon.classList.add('bi-eye');
  }
});

// Toggle visibility for the confirm password field
document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
  const passwordInput = document.getElementById('confirmPassword');
  const toggleIcon = document.getElementById('toggleConfirmIcon');

  if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('bi-eye');
      toggleIcon.classList.add('bi-eye-slash');
  } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('bi-eye-slash');
      toggleIcon.classList.add('bi-eye');
  }
});