const form = document.getElementById('registrationForm');
const fields = ['name', 'roll', 'email', 'course', 'password', 'document'];
function showError(field, message) {
  document.getElementById(field + 'Error').textContent = message;
}
form.addEventListener('submit', (event) => {
  event.preventDefault(); // Keep this example on the client; no server is configured.
  fields.forEach((field) => showError(field, ''));
  document.getElementById('successMessage').textContent = '';
  let valid = true;
  const name = document.getElementById('name').value.trim();
  const roll = document.getElementById('roll').value.trim();
  const email = document.getElementById('email').value.trim();
  const course = document.getElementById('course').value;
  const password = document.getElementById('password').value;
  const fileInput = document.getElementById('document');
  const file = fileInput.files[0];

  if (name.length < 2) { showError('name', 'Enter a name with at least 2 characters.'); valid = false; }
  if (!/^[A-Za-z0-9/-]{3,20}$/.test(roll)) { showError('roll', 'Use 3–20 letters, numbers, / or -.'); valid = false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('email', 'Enter a valid email address.'); valid = false; }
  if (!course) { showError('course', 'Please select a course.'); valid = false; }
  if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)) {
    showError('password', 'Password does not meet the strength requirements.'); valid = false;
  }
  if (!file) { showError('document', 'Please select a document.'); valid = false; }
  else {
    const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!allowed.includes(file.type)) { showError('document', 'Only PDF, JPG or PNG files are allowed.'); valid = false; }
    if (file.size > 2 * 1024 * 1024) { showError('document', 'File must be 2 MB or smaller.'); valid = false; }
  }
  if (valid) document.getElementById('successMessage').textContent = 'Validation successful! This demo does not save data to a server.';
});
