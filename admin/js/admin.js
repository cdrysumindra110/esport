document.addEventListener("DOMContentLoaded", function () {
  var html = document.documentElement;
  var body = document.body;
  var loader = document.getElementById("preloader");
  var collapseBtn = document.querySelector(".admin-menu .collapse-btn");
  var toggleMobileMenu = document.querySelector(".toggle-mob-menu");
  var switchInput = document.querySelector(".switch input");
  var switchLabel = document.querySelector(".switch label");
  var switchLabelText = switchLabel ? switchLabel.querySelector("span:last-child") : null;
  var menuLinks = document.querySelectorAll(".admin-menu a");
  var collapsedClass = "collapsed";
  var lightModeClass = "light-mode";

  if (loader) {
    window.addEventListener("load", function () {
      loader.style.display = "none";
    });
  }

  if (collapseBtn) {
    collapseBtn.addEventListener("click", function () {
      body.classList.toggle(collapsedClass);
      var expanded = this.getAttribute("aria-expanded") === "true";
      this.setAttribute("aria-expanded", expanded ? "false" : "true");
      this.setAttribute("aria-label", expanded ? "expand menu" : "collapse menu");
    });
  }

  if (toggleMobileMenu) {
    toggleMobileMenu.addEventListener("click", function () {
      body.classList.toggle("mob-menu-opened");
      var expanded = this.getAttribute("aria-expanded") === "true";
      this.setAttribute("aria-expanded", expanded ? "false" : "true");
      this.setAttribute("aria-label", expanded ? "open menu" : "close menu");
    });
  }

  menuLinks.forEach(function (link) {
    link.addEventListener("mouseenter", function () {
      var titleNode = this.querySelector("span");
      if (
        titleNode &&
        body.classList.contains(collapsedClass) &&
        window.matchMedia("(min-width: 981px)").matches
      ) {
        this.setAttribute("title", titleNode.textContent.trim());
      } else {
        this.removeAttribute("title");
      }
    });
  });

  if (switchInput && switchLabelText) {
    if (localStorage.getItem("admin-light-mode") === "true") {
      html.classList.add(lightModeClass);
      switchInput.checked = false;
      switchLabelText.textContent = "Light";
    }

    switchInput.addEventListener("input", function () {
      html.classList.toggle(lightModeClass);
      var isLightMode = html.classList.contains(lightModeClass);
      switchLabelText.textContent = isLightMode ? "Light" : "Dark";
      localStorage.setItem("admin-light-mode", isLightMode ? "true" : "false");
    });
  }

  var currentPath = window.location.pathname.toLowerCase();
  menuLinks.forEach(function (link) {
    var href = (link.getAttribute("href") || "").toLowerCase();
    if (!href || href === "#") {
      return;
    }
    if (currentPath.indexOf(href.replace("../", "/")) !== -1 || currentPath.endsWith(href.replace("../admin/", ""))) {
      link.classList.add("is-active");
    }
  });

  var profileDropdownTrigger = document.querySelector(".dropdown > a");
  if (profileDropdownTrigger) {
    profileDropdownTrigger.addEventListener("click", function (event) {
      event.preventDefault();
      var parent = this.parentElement;
      parent.classList.toggle("open");
    });
  }

  var popupMessage = document.getElementById("popup-message");
  if (popupMessage && popupMessage.innerText.trim() !== "") {
    popupMessage.style.display = "block";
    setTimeout(function () {
      popupMessage.style.display = "none";
    }, 4000);
  }

  injectAnalyticsPanel();
});

function injectAnalyticsPanel() {
  var pageContent = document.querySelector(".page-content");
  if (!pageContent || document.querySelector(".analytics-panel")) {
    return;
  }

  var tables = document.querySelectorAll("table");
  var rows = document.querySelectorAll("table tbody tr");
  var forms = document.querySelectorAll("form");
  var actionButtons = document.querySelectorAll("button, .action-button, .view-btn, .delete-btn, .suspend-btn");
  var sections = document.querySelectorAll("section[id]");
  var heading = document.querySelector("h1");
  var titleText = heading ? heading.textContent.trim() : "Admin Overview";

  var panel = document.createElement("section");
  panel.className = "analytics-panel";
  panel.innerHTML = ""
    + createMetricCard("Workspace", titleText, "Current module")
    + createMetricCard("Data Tables", String(tables.length), "Structured datasets")
    + createMetricCard("Visible Rows", String(rows.length), "Operational records")
    + createMetricCard("Forms", String(forms.length), "Input workflows")
    + createMetricCard("Actions", String(actionButtons.length), "Interactive controls")
    + createMetricCard("Sections", String(sections.length), "Panel blocks");

  var grid = pageContent.querySelector(".grid");
  if (grid && grid.parentNode) {
    grid.parentNode.insertBefore(panel, grid.nextSibling);
  } else {
    pageContent.insertBefore(panel, pageContent.firstChild);
  }
}

function createMetricCard(label, value, note) {
  return ""
    + '<article class="analytics-card">'
    + '<span class="analytics-label">' + escapeHtml(label) + "</span>"
    + '<div class="analytics-value">' + escapeHtml(value) + "</div>"
    + '<div class="analytics-note">' + escapeHtml(note) + "</div>"
    + "</article>";
}

function escapeHtml(text) {
  var map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  };
  return String(text).replace(/[&<>"']/g, function (m) {
    return map[m];
  });
}

function showPopup(id, fullName, email, uname, country, city, role, dob) {
  var popupText = document.getElementById("popup-text");
  var popup = document.getElementById("popup");
  if (!popupText || !popup) {
    return;
  }

  popupText.innerHTML = ""
    + "<strong>ID:</strong> " + escapeHtml(id) + "<br>"
    + "<strong>Full Name:</strong> " + escapeHtml(fullName) + "<br>"
    + "<strong>Email:</strong> " + escapeHtml(email) + "<br>"
    + "<strong>Username:</strong> " + escapeHtml(uname) + "<br>"
    + "<strong>Country:</strong> " + escapeHtml(country) + "<br>"
    + "<strong>City:</strong> " + escapeHtml(city) + "<br>"
    + "<strong>Role:</strong> " + escapeHtml(role) + "<br>"
    + "<strong>Date of Birth:</strong> " + escapeHtml(dob) + "<br>";

  popup.style.display = "flex";
}

function closePopup() {
  var popup = document.getElementById("popup");
  if (popup) {
    popup.style.display = "none";
  }
}

