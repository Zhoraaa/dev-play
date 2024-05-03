
window.addEventListener('DOMContentLoaded', () => {
    $('.close-server-message').on('click', function () {
       $(this).closest('div.server-message').animate({
          opacity: 0,
          height: 40,
       },
       250,
       'linear',
       function () {
          $(this).closest('div.server-message').remove();
       })
    });
 });