    document.getElementById("editProfileForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const userName = document.getElementById("userNameInput").value;
    const userEmail = document.getElementById("userEmailInput").value;
    const userPhone = document.getElementById("userPhoneInput").value;
    const userAddress = document.getElementById("userAddressInput").value;
    const userBio = document.getElementById("userBioInput").value;
    const userAvatarInput = document.getElementById("userAvatarInput");

    document.getElementById("userName").innerText = userName;
    document.getElementById("userEmail").innerText = userEmail;
    document.getElementById("emailDetail").innerText = userEmail;
    document.getElementById("phoneDetail").innerText = userPhone;
    document.getElementById("addressDetail").innerText = userAddress;
    document.getElementById("userBio").innerText = userBio;

    if (userAvatarInput.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("avatar").src = e.target.result;
        };
        reader.readAsDataURL(userAvatarInput.files[0]);
    }

    bootstrap.Modal.getInstance(document.getElementById("editProfileModal")).hide();
});