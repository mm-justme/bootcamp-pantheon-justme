(function(Drupal) {
  Drupal.behaviors.latestPostsSwiper = {
    attach(context) {
      once("latestPostsSwiper", ".post-blocks-list.js-swiper", context).forEach(
        el => {
          console.log(el, "test");

          const controls = document.createElement("div");
          const buttonPrev = document.createElement("div");
          const buttonNext = document.createElement("div");

          controls.classList.add("swiper-controls");
          buttonPrev.classList.add("swiper-button-prev");
          buttonNext.classList.add("swiper-button-next");

          el.appendChild(controls);
          controls.appendChild(buttonPrev);
          controls.appendChild(buttonNext);

          new Swiper(el, {
            slidesPerView: 3,
            slidesPerGroup: 3,
            spaceBetween: 16,
            allowTouchMove: false,
            autoHeight: true,
            loop: true,
            loopFillGroupWithBlank: true,
            breakpoints: {
              // мобільно ≤768px: вертикально, по 3 зверху-вниз
              0: {
                direction: 'vertical',
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 12,
                allowTouchMove: false,
                simulateTouch: false,
              },
              769: {
                direction: 'horizontal',
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 16,
              }
            },
            navigation: {
              nextEl: el.querySelector(".swiper-button-next"),
              prevEl: el.querySelector(".swiper-button-prev")
            }
          });
        }
      );
    }
  };
})(Drupal);
