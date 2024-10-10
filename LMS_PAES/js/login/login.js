function login(event) {
  event.preventDefault();

  const sampleEmail = "";
  const samplePass = "";

  const email = document.getElementById("inputEmail").value;
  const password = document.getElementById("inputPassword").value;

  if (email === sampleEmail && password === samplePass) {
    window.location.href = "dashboard.html";
    console.log("LOGGED IN");
  } else {
    alert("Incorrect email or password");
  }

  return false;
}
