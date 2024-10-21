document.addEventListener("DOMContentLoaded", () => {
  // Existing code for managing steps...

  // Admin page functionality
  const sectionSelect = document.getElementById("sectionSelect");
  const contentInput = document.getElementById("contentInput");
  const saveEditBtn = document.getElementById("saveEditBtn");

  const newSectionTitle = document.getElementById("newSectionTitle");
  const newContentInput = document.getElementById("newContentInput");
  const addContentBtn = document.getElementById("addContentBtn");

  const deleteSectionSelect = document.getElementById("deleteSectionSelect");
  const deleteContentBtn = document.getElementById("deleteContentBtn");

  // Populate existing content in the edit form
  sectionSelect.addEventListener("change", () => {
    const selectedStep = sectionSelect.value;
    const stepContent = document.querySelector(`#${selectedStep} p`).innerText;
    contentInput.value = stepContent;
  });

  // Save edited content
  saveEditBtn.addEventListener("click", () => {
    const selectedStep = sectionSelect.value;
    const newContent = contentInput.value;
    document.querySelector(`#${selectedStep} p`).innerText = newContent;
    alert("Content updated successfully");
  });

  // Add new content
  addContentBtn.addEventListener("click", () => {
    const newSection = document.createElement("div");
    newSection.classList.add("step-content");
    newSection.innerHTML = `
            <h2>${newSectionTitle.value}</h2>
            <p>${newContentInput.value}</p>
            <button class="btn btn-secondary prev-btn">Previous</button>
            <button class="btn btn-primary next-btn">Next</button>
        `;
    document.body.appendChild(newSection);
    alert("New content added successfully");
  });

  // Delete content
  deleteContentBtn.addEventListener("click", () => {
    const selectedStep = deleteSectionSelect.value;
    const stepContent = document.querySelector(`#${selectedStep}`);
    stepContent.remove();
    alert("Content deleted successfully");
  });
});
