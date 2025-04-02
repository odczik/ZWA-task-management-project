const stickyElement = document.querySelector('nav');
const observer = new IntersectionObserver(
    ([e]) => e.target.classList.toggle('is-sticky', e.intersectionRatio < 1),
    { rootMargin: '0px 0px 0px 0px', threshold: [1] }
);
observer.observe(stickyElement);



const passwordInput = document.getElementById('register-password');
const confirmPasswordInput = document.getElementById('password-match');
const errorLabel = document.getElementById('error-label');

confirmPasswordInput.addEventListener('input', function() {
    if (confirmPasswordInput.value === passwordInput.value) {
        confirmPasswordInput.setCustomValidity('');

        confirmPasswordInput.classList.remove('error');
        errorLabel.innerHTML = '&nbsp;';
    } else {
        confirmPasswordInput.setCustomValidity('Passwords do not match');

        confirmPasswordInput.classList.add('error');
        errorLabel.innerText = 'Passwords do not match';

        // confirmPasswordInput.reportValidity();
    }
});