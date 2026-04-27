// app.js - Enhanced Theme + Form UI Handler

document.addEventListener('DOMContentLoaded', function () {
  const username = document.getElementById('username');
  const password = document.getElementById('password');
  const signinBtn = document.getElementById('signin-btn');
  const pwToggle = document.getElementById('pw-toggle');
  const logoDark = document.querySelector('.logo-dark');
  const logoLight = document.querySelector('.logo-light');

  // === FORM FUNCTIONALITY ===
  function updateActive(input) {
    const wrapper = input.closest('.animate-input');
    if (!wrapper) return;
    wrapper.classList.toggle('active', input.value.trim() !== '');
  }

  function updateButtonState() {
    signinBtn.disabled = !(username.value.trim() && password.value.trim());
  }

  [username, password].forEach(inp => {
    updateActive(inp);
    inp.addEventListener('focus', () => inp.closest('.animate-input').classList.add('active'));
    inp.addEventListener('blur', () => updateActive(inp));
    inp.addEventListener('input', () => {
      updateActive(inp);
      updateButtonState();
    });
  });

  // Password visibility toggle
  pwToggle.addEventListener('click', function () {
    const isHidden = password.type === 'password';
    password.type = isHidden ? 'text' : 'password';
    pwToggle.textContent = isHidden ? 'Hide' : 'Show';
    password.focus();
  });

  // Button state init
  updateButtonState();

  // === THEME LOGO HANDLER ===
  function updateLogoTheme() {
    const isLight = window.matchMedia('(prefers-color-scheme: light)').matches;
    if (isLight) {
      logoDark.style.display = 'none';
      logoLight.style.display = 'block';
    } else {
      logoDark.style.display = 'block';
      logoLight.style.display = 'none';
    }
  }

  // Initial logo state
  updateLogoTheme();

  // Detect real-time system theme change
  window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', updateLogoTheme);

  // === FORM SUBMIT FEEDBACK ===
  const form = document.getElementById('signin-form');
  form.addEventListener('submit', function () {
    signinBtn.textContent = 'Logging in...';
    signinBtn.disabled = true;
  });
});
