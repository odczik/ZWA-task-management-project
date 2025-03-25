const stickyElement = document.querySelector('nav');
const observer = new IntersectionObserver(
  ([e]) => e.target.classList.toggle('is-sticky', e.intersectionRatio < 1),
  { rootMargin: '0px 0px 0px 0px', threshold: [1] }
);
observer.observe(stickyElement);