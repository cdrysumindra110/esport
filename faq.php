<?php
include('header.php');
?>

<style>
      /* Accordion CSS */
      .accordion {
          max-width: 100%;
          margin: 0 auto;
      }
      .accordion dd {
          display: none;
          padding: 10px;
          font-size: large;
          border: 1px solid goldenrod;
      }
      .accordion dd:first-of-type {
          display: block;
      }
      .accordion dt {
          position: relative;
          background-color: aliceblue;
          box-shadow: 0 1px 4px 0 gray;
          margin-top: 15px;
          padding: 4px 10px;
          cursor: pointer;
          font-size: 18px;
      }
      .accordion dt:hover,
      .accordion dt.expand {
          background-color: darkcyan;
          color: #fff;
      }
      .accordion dt span {
          position: absolute;
          left: 10px;
      }
      .accordion dt::before,
      .accordion dt::after {
          content: "";
          display: inline-block;
          width: 16px;
          height: 3px;
          background-color: #000;
          position: absolute;
          top: 50%;
          right: 10px;
          transform: translate(0, -50%);
          transition: 0.3s;
      }
      .accordion dt::after {
          width: 4px;
          height: 16px;
          right: 16px;
      }
      .accordion dt.expand::after {
          right: 10px;
          width: 16px;
          height: 3px;
      }
      .accordion dt.expand::before,
      .accordion dt.expand::after {
          background-color: white;
      }
    </style>

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="width: 100%; height: 100%; object-fit: cover;background-image:url(img/battleground.gif)">
          <div class="line">
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline" style="text-align: center;">
              FAQ
            </h1>
          </div>
        </header>

        <!-- Section 2 -->
        <section class="section" style="width: 80%; margin: 0 auto;">    
          <h2 class="text-extra-strong text-size-40 text-m-size-40" style="text-align : center">Frequently Asked Questions (FAQs)</h2>
          <p>Welcome to the InfiKnight FAQ page. Here you’ll find answers to the most common questions about our Esports tournaments, registration process, rules, and services.</p>
          <dl class="accordion">
            <dt>1. How do I register for a tournament?</dt>
            <dd>
                To register, simply visit the tournament page, click on the "Register Now" button, and fill out the required details. Ensure all your team or individual information is accurate before submitting.
            </dd>

            <dt>2. Is there a registration fee for participating in tournaments?</dt>
            <dd>
                Yes, some tournaments may require a registration fee, while others are free. The fee will be clearly mentioned on the tournament details page.
            </dd>

            <dt>3. What payment methods do you accept?</dt>
            <dd>
                We accept payments via:<br>
                • Credit/Debit Cards<br>
                • PayPal<br>
                • Online Payment Gateways<br>
                All transactions are secure and processed through trusted providers.
            </dd>

            <dt>4. Can I participate as an individual, or do I need a team?</dt>
            <dd>
                It depends on the tournament format:<br>
                • Solo Tournaments: You can participate individually.<br>
                • Team-Based Tournaments: A team is required to participate.<br>
                Tournament details will specify whether the event is solo, duo, or team-based.
            </dd>

            <dt>5. How do I know if my registration is successful?</dt>
            <dd>
                After registering, you will receive a confirmation email with all the necessary details, including your registration ID and tournament information. Be sure to check your inbox (and spam folder).
            </dd>

            <dt>6. What are the rules for the tournaments?</dt>
            <dd>
                Each tournament has its own set of rules and guidelines, which are listed on the respective tournament page. These include game-specific rules, team behavior, penalties, and disqualification conditions.
            </dd>

            <dt>7. What should I do if I face technical issues during a tournament?</dt>
            <dd>
                If you encounter any technical issues, immediately contact our Support Team via:<br>
                • Live Chat on the website<br>
                • Email: [insert email address]<br>
                • Hotline: [insert phone number]
            </dd>

            <dt>8. How do I get the tournament schedule and updates?</dt>
            <dd>
                You will receive regular updates, including schedules, match timings, and results, via:<br>
                • Your registered email<br>
                • Notifications on our website<br>
                • SMS (if opted-in during registration)
            </dd>

            <dt>9. How are prizes distributed?</dt>
            <dd>
                Winners will be contacted via email or phone, and prize distribution will occur through:<br>
                • Direct bank transfer<br>
                • PayPal or other online payment methods<br>
                Ensure you provide accurate payment details during registration.
            </dd>

            <dt>10. Can I cancel my registration? Will I get a refund?</dt>
            <dd>
                Yes, you can cancel your registration before the tournament begins. Refund policies vary depending on the tournament. Refer to our Refund Policy for more details.
            </dd>

            <dt>11. How do I report unfair gameplay or cheating?</dt>
            <dd>
                If you witness any cheating, hacking, or unfair practices, report it immediately to our support team. Provide evidence such as screenshots, match IDs, or video recordings. We take cheating seriously and enforce strict penalties.
            </dd>

            <dt>12. How can I contact InfiKnight for support?</dt>
            <dd>
                You can reach us through:<br>
                • Email: [insert email address]<br>
                • Live Chat: Available on our website<br>
                • Phone: [insert contact number]<br>
                • Social Media: Follow us on [Facebook | Twitter | Instagram | Discord]
            </dd>

            <dt>13. Do you stream the tournaments live?</dt>
            <dd>
                Yes, selected tournaments are streamed live on our official platforms, including YouTube, Twitch, or Facebook. Check the tournament details for live streaming links.
            </dd>

            <dt>14. Can I partner or sponsor a tournament with InfiKnight?</dt>
            <dd>
                Absolutely! We welcome sponsorship and partnerships. Please contact us at [insert email address] to discuss collaboration opportunities.
            </dd>

            <dt>15. How do I stay updated about upcoming tournaments?</dt>
            <dd>
                To stay in the loop:<br>
                • Subscribe to our newsletter<br>
                • Follow us on social media platforms<br>
                • Check the Upcoming Tournaments section on our website.
            </dd>
          </dl>   
        </section>
        
        <!-- Section 3 -->

        
        
        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(./img/contact_us.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Welcome to our esports hub!<br> Dive into the latest tournaments, team updates, and gaming news. Join the action and be part of our gaming community. </p>
          </div>            
          <div class="line">  
            <div class="s-12 m-12 l-3 center">
              <a href="our-services.php" class="s-12 button border-radius background-primary text-size-20 text-white">Contact Us</a>
            </div>
          </div>
            
          <!-- red full width arrow object -->
          <img class="arrow-object" src="img/object-red.svg" alt="">
        </section>
      </article>  

    </main>
    
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });

    // Accordian js
    let accordDT = jQuery(".accordion dt");
    accordDT.on("click", function () {
      $(this).toggleClass("expand");
      // jQuery(this).next('dd').slideDown(300).siblings('dd').slideUp(500);// only single toggle
      $(this).next("dd").slideToggle(300); //best for responsive toggle
    });
  </script>

<?php include('footer.php'); ?>