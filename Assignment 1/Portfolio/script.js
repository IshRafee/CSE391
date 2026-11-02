(function () {
  "use strict";

  function setText(id, value) {
    var element = document.getElementById(id);
    if (element) {
      element.textContent = value;
    }
  }

  function formatDate(date) {
    if (isNaN(date.getTime())) {
      return "Date unavailable";
    }
    try {
      return date.toLocaleString(undefined, {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    } catch (error) {
      return date.toDateString();
    }
  }

  function updatePageDetails() {
    setText("page-location", window.location.href);
    var modifiedText = document.lastModified;
    var modifiedDate = new Date(modifiedText);
    setText("last-modified", formatDate(modifiedDate));
    setText("copyright-year", String(new Date().getFullYear()));
  }

  function setupThemeToggle() {
    var toggle = document.getElementById("theme-toggle");
    if (!toggle) {
      return;
    }

    var savedTheme = null;
    try {
      savedTheme = window.localStorage.getItem("isthiaq-theme");
    } catch (error) {
      savedTheme = null;
    }

    if (savedTheme === "dark") {
      document.body.classList.add("dark-mode");
    }

    function updateButton() {
      var isDark = document.body.classList.contains("dark-mode");
      toggle.textContent = isDark ? "☼" : "◐";
      toggle.setAttribute("aria-label", isDark ? "Switch to light mode" : "Switch to dark mode");
      toggle.setAttribute("title", isDark ? "Switch to light mode" : "Switch to dark mode");
    }

    updateButton();
    toggle.addEventListener("click", function () {
      var isDark = document.body.classList.toggle("dark-mode");
      try {
        window.localStorage.setItem("isthiaq-theme", isDark ? "dark" : "light");
      } catch (error) {
        /* Theme still works for the current page when storage is unavailable. */
      }
      updateButton();
    });
  }

  function setupContactForm() {
    var form = document.getElementById("contact-form");
    if (!form) {
      return;
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var recipient = "your.email@example.com";
      var name = document.getElementById("visitor-name").value.trim();
      var email = document.getElementById("visitor-email").value.trim();
      var message = document.getElementById("visitor-message").value.trim();
      var subject = encodeURIComponent("Website message from " + name);
      var body = encodeURIComponent("Name: " + name + "\nEmail: " + email + "\n\nMessage:\n" + message);
      var feedback = document.getElementById("form-feedback");

      if (!name || !email || !message || email.indexOf("@") < 1 || email.indexOf(".") < email.indexOf("@") + 2) {
        feedback.textContent = "Please enter your name, a valid email address, and a message before continuing.";
        return;
      }

      if (recipient === "your.email@example.com") {
        feedback.textContent = "Demo mode: replace your.email@example.com in index.html and js/script.js with your real email before publishing.";
      } else {
        feedback.textContent = "Your email app should open with the message draft. Press Send in that app to send it.";
      }
      window.location.href = "mailto:" + recipient + "?subject=" + subject + "&body=" + body;
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    updatePageDetails();
    setupThemeToggle();
    setupContactForm();
  });
}());
