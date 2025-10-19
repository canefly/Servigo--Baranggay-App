<!-- 📮 Resident Feedback Modal -->
<div id="feedbackModal" class="modal hidden">
  <div class="modal-card">
    <img src="asset/logo.png" alt="Barangay Logo" class="modal-logo">
    <h3 class="modal-title">Was this helpful?</h3>
    <p class="modal-desc">Your quick feedback helps us improve our barangay services.</p>
    
    <div class="feedback-actions">
      <button class="thumb-btn up" id="thumbUp">
        <i>👍</i><span>Yes</span>
      </button>
      <button class="thumb-btn down" id="thumbDown">
        <i>👎</i><span>No</span>
      </button>
    </div>

    <div id="feedbackReasonBox" class="feedback-reason hidden">
      <label for="feedbackReason">Tell us what went wrong (optional):</label>
      <textarea id="feedbackReason" rows="3" placeholder="Example: The page took too long to load..."></textarea>
      <button id="submitFeedback" class="submit-btn">Submit</button>
    </div>
  </div>
  <div id="modalBackdrop" class="modal-backdrop"></div>
</div>

<style>
  /* 🌆 Servigo Modal Theme */
  .modal.hidden { display: none; }
  .modal { position: fixed; inset: 0; z-index: 999; display: flex; align-items: center; justify-content: center; }

  .modal-card {
    position: relative;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    width: 360px;
    padding: 24px 20px;
    text-align: center;
    animation: fadeUp 0.3s ease;
    font-family: "Poppins", sans-serif;
  }

  .modal-logo {
    width: 48px;
    height: 48px;
    margin-bottom: 10px;
  }

  .modal-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1d3557;
    margin-bottom: 6px;
  }

  .modal-desc {
    font-size: 0.95rem;
    color: #6b7280;
    margin-bottom: 18px;
  }

  .feedback-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
  }

  .thumb-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 12px;
    padding: 10px 18px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 1rem;
  }

  .thumb-btn.up {
    background: #e0f2fe;
    color: #0369a1;
  }
  .thumb-btn.up:hover {
    background: #bae6fd;
  }

  .thumb-btn.down {
    background: #fee2e2;
    color: #991b1b;
  }
  .thumb-btn.down:hover {
    background: #fecaca;
  }

  .feedback-reason {
    margin-top: 16px;
    text-align: left;
    animation: fadeIn 0.2s ease;
  }

  .feedback-reason label {
    display: block;
    font-size: 0.9rem;
    color: #4b5563;
    margin-bottom: 6px;
  }

  .feedback-reason textarea {
    width: 100%;
    resize: none;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    padding: 8px;
    font-family: inherit;
    font-size: 0.9rem;
    outline: none;
  }

  .feedback-reason textarea:focus {
    border-color: #3b82f6;
  }

  .submit-btn {
    display: block;
    width: 100%;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 8px 0;
    margin-top: 10px;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background 0.2s;
  }

  .submit-btn:hover {
    background: #2563eb;
  }

  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(3px);
    z-index: -1;
  }

  @keyframes fadeUp {
    from { transform: translateY(10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>

<script>
  const modal = document.getElementById("feedbackModal");
  const backdrop = document.getElementById("modalBackdrop");
  const thumbUp = document.getElementById("thumbUp");
  const thumbDown = document.getElementById("thumbDown");
  const reasonBox = document.getElementById("feedbackReasonBox");
  const submitFeedback = document.getElementById("submitFeedback");

  function openFeedbackModal() {
    modal.classList.remove("hidden");
  }

  function closeFeedbackModal() {
    modal.classList.add("hidden");
    reasonBox.classList.add("hidden");
  }

  thumbUp.addEventListener("click", () => {
    sendFeedback("positive");
    closeFeedbackModal();
  });

  thumbDown.addEventListener("click", () => {
    reasonBox.classList.remove("hidden");
  });

  submitFeedback.addEventListener("click", () => {
    const reason = document.getElementById("feedbackReason").value.trim();
    sendFeedback("negative", reason);
    closeFeedbackModal();
  });

  async function sendFeedback(type, reason = "") {
    try {
      await fetch("/saveFeedback.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          feedback_type: type,
          feedback_reason: reason || null,
          page: window.location.pathname,
          timestamp: new Date().toISOString()
        })
      });
      console.log("Feedback sent:", type, reason);
    } catch (err) {
      console.error("Error submitting feedback:", err);
    }
  }
</script>
