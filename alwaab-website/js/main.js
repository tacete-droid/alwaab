/* Al Waab Building Materials — UAE | interactions */
(function () {
  "use strict";

  // Header shadow on scroll
  var header = document.querySelector(".header");
  function onScroll() {
    if (!header) return;
    header.classList.toggle("scrolled", window.scrollY > 12);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  // Mobile menu
  var burger = document.querySelector(".hamburger");
  var menu = document.querySelector(".menu");
  if (burger && menu) {
    burger.addEventListener("click", function () {
      menu.classList.toggle("open");
    });
    menu.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () { menu.classList.remove("open"); });
    });
  }

  // Reveal on scroll
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        e.target.classList.add("in");
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll(".reveal").forEach(function (el) { io.observe(el); });

  // Animated counters
  var counted = false;
  function runCounters() {
    if (counted) return;
    document.querySelectorAll("[data-count]").forEach(function (el) {
      var target = parseFloat(el.getAttribute("data-count"));
      var suffix = el.getAttribute("data-suffix") || "";
      var dur = 1600, start = null;
      function step(ts) {
        if (!start) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var val = Math.floor(p * target);
        el.textContent = val.toLocaleString() + suffix;
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString() + suffix;
      }
      requestAnimationFrame(step);
    });
    counted = true;
  }
  var band = document.querySelector(".statband");
  if (band) {
    var io2 = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) runCounters(); });
    }, { threshold: 0.3 });
    io2.observe(band);
  }

  // Contact form → ERP system
  var form = document.querySelector("#quoteForm");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var note = form.querySelector(".form-note");
      var submitBtn = form.querySelector('button[type="submit"]');
      var name = document.getElementById("name");
      var company = document.getElementById("company");
      var email = document.getElementById("email");
      var phone = document.getElementById("phone");
      var product = document.getElementById("product");
      var msg = document.getElementById("msg");

      if (!window.AlwaabErp) {
        if (note) {
          note.style.display = "block";
          note.style.color = "#b42318";
          note.style.background = "#fef3f2";
          note.textContent = "System configuration missing. Please contact us at info@alwaab.ae";
        }
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Sending...";
      }

      window.AlwaabErp.submitQuoteRequest({
        name: name ? name.value : "",
        company: company ? company.value : "",
        email: email ? email.value : "",
        phone: phone ? phone.value : "",
        product_interest: product ? product.value : "",
        message: msg ? msg.value : "",
        page: "contact",
      }).then(function () {
        if (note) {
          note.style.display = "block";
          note.style.color = "#0a8a3a";
          note.style.background = "#e9f9ef";
          note.textContent = "✓ Thank you! Your request has been received. Our team will contact you within 24 hours.";
        }
        form.reset();
      }).catch(function (err) {
        if (note) {
          note.style.display = "block";
          note.style.color = "#b42318";
          note.style.background = "#fef3f2";
          note.textContent = "Could not send request. Please email info@alwaab.ae or call +971 4 251 4228.";
        }
        console.error(err);
      }).finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Send Request<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
        }
      });
    });
  }

  // Footer year
  var y = document.querySelector("#year");
  if (y) y.textContent = new Date().getFullYear();
})();
