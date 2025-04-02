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

// Registration AJAX
const registerButton = document.getElementById('register-button');
const registerForm = document.querySelector('.nav-register-modal');
if(registerForm != null) registerForm.addEventListener('submit', function(event) {
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
        console.log('Response:', data);
    })
    .catch(() => {
        window.location.href = '/dashboard';
    });
});