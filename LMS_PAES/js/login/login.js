function login(){
    const sampleEmail = 'admin@email.com';
    const samplePass = 'pass123';

    const email = document.getElementById('inputEmail').value;
    const password = document.getElementById('inputPassword').value;

    if(email === sampleEmail && password === samplePass){
        window.location.href = "dashboard.html"
        console.log('LOGGED IN');
    }
    else{
        
    }
}