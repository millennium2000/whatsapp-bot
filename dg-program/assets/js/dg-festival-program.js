(function () {
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".dg-has-venue-tooltip").forEach(function (element) {
      element.removeAttribute("title");

      if (!element.hasAttribute("tabindex")) {
        element.setAttribute("tabindex", "0");
      }
    });
  });
})();
