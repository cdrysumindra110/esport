<?php
include('header.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize and validate form inputs
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $subject = mysqli_real_escape_string($conn, $_POST['subject']);
  $message = mysqli_real_escape_string($conn, $_POST['message']);

  // Insert data into the database
  $sql = "INSERT INTO contact (name, email, subject, message) 
          VALUES ('$name', '$email', '$subject', '$message')";

  if ($conn->query($sql) === TRUE) {
    $success_message = "Thank you for contacting us.";
  } else {
    $error_message = "Error: " . $sql . "<br>" . $conn->error;
  }
}

// Close connection
$conn->close();

?>

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/services.gif);">
          <div class="line">
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Our Services</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>

        <!-- Section 1 -->
        <section class="full-width background-white">
          <div class="s-12 m-12 l-4">
            <!-- Change the background image -->  
            <div style="background-image: url(img/our_service.jpg);" class="contact-image" ></div>
          </div>
          <div class="s-12 m-12 l-4 text-center">
            <div class="padding-2x">
              <i class="icon-sli-location-pin text-primary text-size-30 center"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Company Address</h2>                
              <p>
                 Bafal Marga,<br>
                 Kathmandu, Nepal
              </p> 
              
              <i class="icon-sli-envelope text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">E-mail</h2>                
              <a class="text-primary-hover" href="mailto:infiknightesports@gmail.com">infiknightesports@gmail.com</a><section>
              <a class="text-primary-hover" href="mailto:contact@infiknightesports.com">contact@infiknightesports.com</a>
              
              <i class="icon-sli-earphones-alt text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Phone Numbers</h2>                
              <p>
                +977 9864666601<br>
                +977 9864666601
              </p> 
            </div>
          </div>
          <div class="s-12 m-12 l-4">
            <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1459734.5702753505!2d16.91089086619977!3d48.577103681657675!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ssk!2ssk!4v1457640551761" width="100%" height="600" frameborder="0" style="border:0"></iframe> -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3224.2555422694672!2d85.27984737496206!3d27.704231625629888!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19e90cf303c5%3A0x7e0ccc224c0c39ae!2sInfiKnight%20Esports!5e1!3m2!1sen!2snp!4v1739861973629!5m2!1sen!2snp" width="100%" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

          </div>
        </section>
        
        <!-- Section 3 -->
        <section class="section background-image" style="background-image:url(img/contact_bg.jpg)">
          <div class="s-12 m-12 l-4 center">
            <h3 class="text-white text-size-60 margin-bottom-20 text-center">Contact Form</h3>

            <form id="contactForm" name="contactForm" class="customform text-white" method="post" enctype="multipart/form-data" action="our-services.php">
              <div class="line">
                <div class="margin">
                  <div class="s-12 m-12 l-6">
                    <input name="email" id="email" class="required email" placeholder="Your e-mail" title="Your e-mail" type="text" required />
                  </div>
                  <div class="s-12 m-12 l-6">
                    <input name="name" id="name" class="name" placeholder="Your name" title="Your name" type="text" required />
                  </div>
                </div>
              </div>            

              <div class="line">       
                <div class="s-12">
                  <input name="subject" id="subject" class="required subject" placeholder="Subject" title="Subject" type="text" required />
                  <p class="subject-error form-error">Please enter your subject.</p>
                </div>
                <div class="s-12">
                  <textarea name="message" id="message" class="required message" placeholder="Your message" rows="3" required></textarea>
                  <p class="message-error form-error">Please enter your message.</p>
                </div>
                <div class="s-12">
                  <button class="button border-radius text-white background-primary" type="submit">Submit</button>
                </div>
              </div>    
            </form>
          </div>  
                
        </section>

     <!-- Section Services -->
     <section class="section">      
      <div class="line">
        <div class="margin2x">
           
           <!-- Image 1 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/team-management.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Team Management</h3>
                <p>
                  Our team management services help you build and maintain a strong, 
                  competitive team. We offer support in player recruitment, training 
                  schedules, and performance analysis. Our goal is to help your team
                  reach its full potential by providing the resources and guidance 
                  needed to succeed in the competitive esports landscape.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 2 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/event-planning.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Event Planning</h3>
                <p>
                  From small-scale events to large-scale tournaments, our event planning
                  services ensure every detail is covered. We manage venue selection, 
                  logistics, and on-site operations to create a memorable and smooth experience
                   for participants and spectators. Our experienced team coordinates every aspect
                    of your event, so you can focus on the action.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 3 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/sponsored.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Sponsorship</h3>
                <p>
                  We connect your esports events with potential sponsors to enhance their visibility 
                  and financial support. Our sponsorship services include identifying suitable partners, 
                  negotiating terms, and managing sponsorship agreements. We aim to create mutually 
                  beneficial relationships that provide value to both sponsors and your event.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 4 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/media-coverage.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Media Coverage</h3>
                <p>
                  Effective media coverage is crucial for promoting esports events. We offer comprehensive 
                  media services, including press releases, social media management, and live streaming. 
                  goal is to maximize your event’s exposure and reach a broader audience, ensuring that
                  your event garners the attention it deserves.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 5 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/custom-service.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Custom Services</h3>
                <p>
                  We understand that every esports event is unique, and we offer custom services tailored 
                  to your specific needs. Whether you require bespoke event features, specialized tournament 
                  formats, or unique promotional strategies, our team is here to design and deliver solutions 
                  that align with your vision and objectives.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 6 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/tournaments.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Tournaments</h3>
                <p>
                  We specialize in organizing and executing esports tournaments that bring together players and 
                  fans from around the world. Our tournaments are meticulously planned to ensure a seamless experience, 
                  from initial registration to the final matches. We handle all aspects, including scheduling, match management, 
                  and prize distribution, ensuring a professional and exciting event for everyone involved.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
        </div>                                                                                                
      </div>     
    </section>
    <!-- Section 9 -->
    <section>
      <!-- red full width arrow object -->
      <img class="arrow-object" src="img/object-red.svg" alt="">
    </section>
  
<?php include('footer.php'); ?>