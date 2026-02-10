document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing slider…');

    const swiper = new Swiper('.swiper', {
        slidesPerView: 1,
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
        pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
    });

    console.log('Swiper instance:', swiper);
});
