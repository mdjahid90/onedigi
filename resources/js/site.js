import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.social-menu ul li a');

    buttons.forEach((btn) => {
        btn.addEventListener('click', function (event) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('span');

            ripple.classList.add('ripple');
            ripple.style.left = `${event.clientX - rect.left}px`;
            ripple.style.top = `${event.clientY - rect.top}px`;

            this.appendChild(ripple);

            window.setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
