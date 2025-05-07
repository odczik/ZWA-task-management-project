const stickyElement = document.querySelector('nav');
const observer = new IntersectionObserver(
    ([e]) => e.target.classList.toggle('is-sticky', e.intersectionRatio < 1),
    { rootMargin: '0px 0px 0px 0px', threshold: [1] }
);
observer.observe(stickyElement);


// Password confirmation for registration
const passwordInput = document.getElementById('register-password');
const confirmPasswordInput = document.getElementById('password-match');
const errorLabel = document.getElementById('error-label');
if(confirmPasswordInput != null) confirmPasswordInput.addEventListener('input', function() {
    if (confirmPasswordInput.value === passwordInput.value) {
        confirmPasswordInput.classList.remove('error');
        errorLabel.innerHTML = '&nbsp;';
    } else {
        confirmPasswordInput.classList.add('error');
        errorLabel.innerText = 'Passwords do not match';
    }
});

// Login AJAX
const navLoginForm = document.querySelector('.nav-login-modal');

navLoginForm?.addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(navLoginForm);

    fetch('/login', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        alert(data.message);
    })
    .catch(() => {
        window.location.href = '/dashboard';
    });
});

// Registration AJAX
const registerForm = document.querySelector('.nav-register-modal');
registerForm?.addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(registerForm);

    fetch('/register', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        alert(data.message);
    })
    .catch(() => {
        window.location.href = '/dashboard';
    });
});