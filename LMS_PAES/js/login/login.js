document.getElementById('togglePassword').addEventListener('click', function () {
  const passwordInput = document.getElementById('inputPassword');
  const toggleIcon = document.getElementById('toggleIcon');
  
  // Toggle password visibility
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