// Declare refreshData function or import it
function refreshData() {
  console.log("Data refreshed")
}

// Auto-refresh data every 30 seconds
setInterval(() => {
  if (typeof refreshData === "function") {
    refreshData()
  }
}, 30000)

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    document.querySelector(this.getAttribute("href")).scrollIntoView({
      behavior: "smooth",
    })
  })
})

// Add loading animation to cards when refreshing
function showLoading(element) {
  const originalContent = element.innerHTML
  element.innerHTML = '<div class="loading"></div>'

  setTimeout(() => {
    element.innerHTML = originalContent
  }, 1000)
}

// Format currency for Nepal
function formatCurrency(amount) {
  return new Intl.NumberFormat("ne-NP", {
    style: "currency",
    currency: "NPR",
    minimumFractionDigits: 2,
  }).format(amount)
}

// Format numbers with commas
function formatNumber(num) {
  return new Intl.NumberFormat("ne-NP").format(num)
}

// Animate counter numbers
function animateCounter(element, start, end, duration) {
  let startTimestamp = null
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp
    const progress = Math.min((timestamp - startTimestamp) / duration, 1)
    const current = Math.floor(progress * (end - start) + start)
    element.textContent = formatNumber(current)
    if (progress < 1) {
      window.requestAnimationFrame(step)
    }
  }
  window.requestAnimationFrame(step)
}

// Initialize animations when page loads
document.addEventListener("DOMContentLoaded", () => {
  // Animate counter values
  const counters = document.querySelectorAll(".card-value")
  counters.forEach((counter) => {
    const value = Number.parseFloat(counter.textContent.replace(/[^0-9.-]+/g, ""))
    if (!isNaN(value)) {
      counter.textContent = "0"
      animateCounter(counter, 0, value, 2000)
    }
  })

  // Add hover effects to cards
  const cards = document.querySelectorAll(".card")
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-10px) scale(1.02)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)"
    })
  })
})

// Real-time clock
function updateClock() {
  const now = new Date()
  const timeString = now.toLocaleString("ne-NP", {
    timeZone: "Asia/Kathmandu",
    hour12: true,
  })

  const clockElement = document.getElementById("current-time")
  if (clockElement) {
    clockElement.textContent = timeString
  }
}

// Update clock every second
setInterval(updateClock, 1000)
updateClock() // Initial call

// Toast notification system
function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `toast toast-${type}`
  toast.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
        <span>${message}</span>
    `

  document.body.appendChild(toast)

  // Animate in
  setTimeout(() => toast.classList.add("show"), 100)

  // Remove after 3 seconds
  setTimeout(() => {
    toast.classList.remove("show")
    setTimeout(() => document.body.removeChild(toast), 300)
  }, 3000)
}

// Add toast styles
const toastStyles = `
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    z-index: 10000;
}

.toast.show {
    transform: translateX(0);
}

.toast-success { border-left: 4px solid #27ae60; }
.toast-error { border-left: 4px solid #e74c3c; }
.toast-info { border-left: 4px solid #3498db; }
`

// Inject toast styles
const styleSheet = document.createElement("style")
styleSheet.textContent = toastStyles
document.head.appendChild(styleSheet)
