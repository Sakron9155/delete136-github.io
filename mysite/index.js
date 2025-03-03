document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll('nav ul li a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    const featuredItems = document.querySelectorAll('.featured-item');
    let currentIndex = 0;

    function showNextItem() {
        featuredItems[currentIndex].style.display = 'none';
        currentIndex = (currentIndex + 1) % featuredItems.length;
        featuredItems[currentIndex].style.display = 'block';
    }

    setInterval(showNextItem, 5000);
});
