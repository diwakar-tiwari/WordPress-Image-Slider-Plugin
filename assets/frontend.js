jQuery(document).ready(function ($) {
  $(".image-slider").slick({
    autoplay: true,
    autoplaySpeed: 5000,
    dots: true,
    arrows: false,
    infinite: true,
    speed: 500,
    fade: true,
    cssEase: "linear",
    adaptiveHeight: true,
    mobileFirst: true,
    lazyLoad: "ondemand",
    lazyLoadEager: 2,
    accessibility: false,
    swipeToSlide: true,
    touchMove: true,
    responsive: [
      {
        breakpoint: 768,
        settings: {
          dots: false,
          arrows: true,
          prevArrow:
            '<button type="button" class="slick-prev"><span class="screen-reader-text">Previous</span></button>',
          nextArrow:
            '<button type="button" class="slick-next"><span class="screen-reader-text">Next</span></button>',
        },
      },
    ],
  });
});
