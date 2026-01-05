<?php
include('header.php');
    
// Fetch tournament data
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS host_username 
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id 
        ORDER BY t.id"; 

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();  

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
    } else {
        $error_message = "No tournament data found.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament statement: " . $conn->error;
}



// SQL query to fetch the latest articles
$sql = "SELECT * FROM news_articles ORDER BY updated_at DESC"; // Sorting by latest updated_at
$result = $conn->query($sql);

// Check if there are any articles
if ($result->num_rows > 0) {
    // Store fetched articles in an array
    $articles = [];
    while ($row = $result->fetch_assoc()) {
      if (!empty($row['image'])) {
        $image_path = 'uploads/' . $row['image'];
    
        // Check if the image file exists
        if (file_exists($image_path) && is_readable($image_path)) {
            $row['image'] = $image_path;
        } else {
            // If file does not exist, use default image
            $row['image'] = 'img/dash-logo.png';
        }
    } else {
        // If no image provided, use default
        $row['image'] = 'img/dash-logo.png';
    }
    
        
        // Store the article in the array
        $articles[] = $row;
    }
} else {
    // If no articles are found
    $articles = [];
}
$conn->close();
?>

<link rel="stylesheet" href="./css/leaderboard.css?ver=1.0">

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>News</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>

        <div class="news-container">
          <header class="news-header">
              <h1>Latest News</h1>
              <div class="search-container">
                  <input type="search" placeholder="Search" class="search-input" />
                  <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="11" cy="11" r="8"></circle>
                      <path d="m21 21-4.3-4.3"></path>
                  </svg>
              </div>
          </header>
      
          <div class="tab-content">
            <!-- Latest News Tab -->
            <div class="tab active" data-tab="latest" style="width: 100%;">
                <?php if (!empty($articles)): ?>
                    <!-- Loop through the articles and display them -->
                    <?php foreach ($articles as $article): ?>
                        <div class="news-card">
                            <!-- Display the article image -->
                            <img src="<?= htmlspecialchars($article['image']) ?>" alt="Article Image" class="news-image" />
                            <div class="news-details">
                                <p>By InfiKnight Gaming Community | Updated at <?= htmlspecialchars(date('Y-m-d', strtotime($article['updated_at']))) ?></p>
                                <h2><?= htmlspecialchars($article['title']) ?></h2>
                                <p><?= htmlspecialchars($article['description']) ?></p>
                                <a href="article_detail.php?id=<?= $article['id'] ?>" class="read-more-link">Read More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No articles available at the moment.</p>
                <?php endif; ?>
            </div>
          </div>
        </div>




        <section class="section-top-bottom-padding">
            <div class="line">
                <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Upcoming Live Events</h2>
            </div>

            <!-- Image / Text Carousel -->
            <div class="carousel-center owl-carousel carousel-main carousel-hide-pagination nav-bottom text-center">

                <?php
                // Assuming $tournaments array is populated from the fetch code
                if (!empty($tournaments)) {
                    foreach ($tournaments as $tournament) {
                        $tournamentName = htmlspecialchars($tournament['tname']);
                        $bannerImg = htmlspecialchars($tournament['bannerimg']);
                        $startDate = $tournament['sdate']; 
                        $startTime = $tournament['stime']; 
                        
                        $tournamentDateTime = $startDate . ' ' . $startTime;
                        
                        $tournamentTimestamp = strtotime($tournamentDateTime);
                        $currentTimestamp = time(); 
          
                        if ($currentTimestamp >= $tournamentTimestamp) {

                            ?>
                          
                            <?php
                        } else {

                            ?>
                            <div class="item">
                                <div class="image-with-text-overlay">
                                    <div class="image-text-overlay">
                                        <div class="image-text-overlay-content padding-2x">
                                            <!-- Text -->
                                            <p class="text-orange text-size-30 margin-bottom-10"><?php echo $tournamentName; ?></p>
                                            <h3 class="text-white text-size-30 text-strong">Starting Soon</h3>
                                            <p class="text-white">The tournament will start on <?php echo date('F j, Y, g:i a', $tournamentTimestamp); ?>.</p> 
                                        </div> 
                                    </div>
                                    <!-- Photo -->
                                    <?php if (!empty($tournament['bannerimg'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tournament['bannerimg']); ?>" alt="Tournament Banner" style="width: 100%; height: 450px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="./img/dash-logo.png" alt="Default Tournament Banner" style="width: 100%; height: 450px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo "No tournament data found.";
                }
                ?>
            </div>
        </section>

        <!-- Section Videos Section --> 
        <section class="section line-full-width">
          <div class="line">
              <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Live Events</h2>
          </div>

          <div class="margin">      
            <?php
                // Assuming $tournaments array is populated from the fetch code
                if (!empty($tournaments)) {
                    foreach ($tournaments as $tournament) {
                        $tournamentName = htmlspecialchars($tournament['tname']);
                        $channelName = htmlspecialchars($tournament['channel_name']);
                        $startDate = $tournament['sdate']; 
                        $startTime = $tournament['stime']; 

                        $tournamentDateTime = $startDate . ' ' . $startTime;
                        

                        $tournamentTimestamp = strtotime($tournamentDateTime);
                        $currentTimestamp = time(); 

                        if ($currentTimestamp >= $tournamentTimestamp) {

                            ?>
                            <div class="s-12 m-6">
                                <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
                          
                                    <h1><?php echo $tournamentName; ?></h1>
                                    <!-- Twitch Embed only  -->
                                    <iframe 
                                        src="https://player.twitch.tv/?channel=<?php echo urlencode($channelName); ?>&parent=localhost&autoplay=true" 
                                        frameborder="0" 
                                        allowfullscreen="true" 
                                        scrolling="no" 
                                        height="365" 
                                        width="700">
                                    </iframe>
                                </a>    
                            </div>
                            <?php
                        } else {

                        }
                    }
                } else {
                    echo "No tournament data found.";
                }
            ?>


            <!-- <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
              <h1>Games Highlights</h1>
                <iframe width="700" height="365" src="https://www.youtube.com/embed/u1oqfdh4xBY?si=vhWBHZT9TCSuW0Mi" 
                title="Pubg Tournaments" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
              <h1>Games Highlights</h1>
                <iframe width="700" height="365" src="https://www.youtube.com/embed/oq2Rz2I11l0?si=SMtxcxt0eeu_LMoK" 
                title="Free Fire Tournament" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
              <h1>Games Highlights</h1>
                <iframe width="700" height="365" src="https://www.youtube.com/embed/4N3xwEtLpu0?si=t__WCvNzt33pjJZi&amp;start=10" 
                title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; 
                gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
              </a>	
            </div>        -->
          </div>
        </section>
        
        <!-- Section 8 -->
        <section class="section background-grey">      
          <div class="line">
            <div class="margin2x">
              <div class="s-12 m-12 l-6">
                
                <h3 class="text-size-40 text-m-size-25"><b>Satisfied</b> Clients</h3>
                <div class="carousel-default owl-carousel carousel-hide-arrows text-left">
                  <div class="item">
                    <div class="s-12">
                      <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                      <p class="margin-bottom">InfiKnight Esports exceeded our expectations in every way. 
                        Their commitment to quality and their ability to engage fans is unparalleled. 
                        We couldn't be happier with the results.</p>
                      <p class="text-primary text-size-16"><strong>Maria Garcia</strong> / Event Coordinator / GameFest</p>
                    </div>
                  </div>
                  
                  <div class="item">
                    <div class="s-12">
                      <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                      <p class="margin-bottom">Working with InfiKnight Esports has been a game-changer for us. Their innovative approach and dedication to excellence truly set them apart. Our gaming events have never been more exciting or professionally managed.</p>
                      <p class="text-primary text-size-16"><strong>Alex Johnson</strong> / Marketing Director / eSports Central</p>
                    </div>
                  </div>
                  
                  <div class="item">
                    <div class="s-12">
                      <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                      <p class="margin-bottom">The team at InfiKnight Esports is incredible. They bring a level of passion and expertise that’s unmatched in the industry. Their support has been invaluable to our esports initiatives.</p>
                      <p class="text-primary text-size-16"><strong>Jordan Smith </strong> / Team Manager / ProGamer League </p>
                    </div>
                  </div>

                  <div class="item">
                    <div class="s-12">
                      <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                      <p class="margin-bottom">InfiKnight Esports has transformed how we approach competitive gaming. Their cutting-edge solutions and exceptional service have made them a key partner in our success.</p>
                      <p class="text-primary text-size-16"><strong> Taylor Brown </strong> / CEO / Ultimate Gaming Arena </p>
                    </div>
                  </div>
                  
                </div>
                
              </div>
              <div class="s-12 m-12 l-6">
                <h3 class="text-size-40 text-m-size-25 margin-bottom-30">The Latest From <b>Our Blog</b></h3> 
                <div class="carousel-default owl-carousel carousel-hide-arrows text-left">
                  
                  <div class="item">
                    <div class="margin margin-bottom-30">
                      <div class="s-12 m-3 l-3">
                        <a class="image-hover-zoom margin-m-bottom-30" href="/">
                          <img src="img/img-04.jpg" alt="">
                        </a>  
                      </div>
                      <div class="s-12 m-9 l-9">
                        <h4><a class="text-dark text-primary-hover text-strong" href="/">Unlocking the Secrets of a Successful Esports Event</a></h4>
                        <p>Alex Johnson shares insider tips on what makes a gaming event truly unforgettable. Discover how to captivate your audience and manage logistics like a pro.</p>
                        <a class="text-more-info text-primary" href="/">Read more</a>
                      </div>  
                    </div> 
                  </div>

                  <div class="item">
                    <div class="margin margin-bottom-30">
                      <div class="s-12 m-3 l-3">
                        <a class="image-hover-zoom margin-m-bottom-30" href="/">
                          <img src="img/img-03.jpg" alt="">
                        </a>  
                      </div>
                      <div class="s-12 m-9 l-9">
                        <h4><a class="text-dark text-primary-hover text-strong" href="/">Maximizing Your Esports Brand</a></h4>
                        <p>Maria Garcia explores cutting-edge marketing strategies that can elevate your esports brand. Learn how to create impactful campaigns and connect with your target audience.</p>
                        <a class="text-more-info text-primary" href="/">Read more</a>
                      </div>  
                    </div> 
                  </div>
                  
                  <div class="item">
                    <div class="margin margin-bottom-30">
                      <div class="s-12 m-3 l-3">
                        <a class="image-hover-zoom margin-m-bottom-30" href="/">
                          <img src="img/parallax-04.jpg" alt="">
                        </a>  
                      </div>
                      <div class="s-12 m-9 l-9">
                        <h4><a class="text-dark text-primary-hover text-strong" href="/">The Future of Esports Arenas</a></h4>
                        <p>Taylor Brown discusses the latest trends in esports arenas and what to expect in the coming years. Get a glimpse into the innovations shaping the future of competitive gaming spaces.</p>
                        <a class="text-more-info text-primary" href="/">Read more</a>
                      </div>  
                    </div> 
                  </div>

                  <div class="item">
                    <div class="margin margin-bottom-30">
                      <div class="s-12 m-3 l-3">
                        <a class="image-hover-zoom margin-m-bottom-30" href="/">
                          <img src="img/img-11.jpg" alt="">
                        </a>  
                      </div>
                      <div class="s-12 m-9 l-9">
                        <h4><a class="text-dark text-primary-hover text-strong" href="/">Forging Strong Partnerships in Esports</a></h4>
                        <p>Casey Lee provides advice on building successful partnerships and collaborations within the esports industry. Discover how strategic alliances can drive growth and success.”</p>
                        <a class="text-more-info text-primary" href="/">Read more</a>
                      </div>  
                    </div> 
                  </div>
                  
                </div>
              </div>
            </div>
          </div>                                                                                     
        </section>

        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(./img/contact_us.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Welcome to our esports hub!<br>
            Dive into the latest tournaments, team updates, and gaming news. Join the action and be part of our gaming community.</p>
          </div>            
          <div class="line">  
            <div class="s-12 m-12 l-3 center">
              <a href="our-services.html" class="s-12 button border-radius background-primary text-size-20 text-white">Contact Us</a>
            </div>
          </div>
            
          <!-- red full width arrow object -->
          <img class="arrow-object" src="img/object-red.svg" alt="">
        </section>

<?php include('footer.php'); ?>