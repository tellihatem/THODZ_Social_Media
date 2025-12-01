
<div class="news_feed">
    <div class="news_feed_title">
        <?php
            if (isset($ROW_USER['profileimg']) && !empty($ROW_USER['profileimg'])){
                $image  = $ROW_USER['profileimg'];
            }else{
                $image = "./images/user_female.jpg";
                if ($ROW_USER['gender'] == 'male'){
                    $image = "./images/user_male.jpg";
                }
            }
        ?>
        <img src="<?php echo $image ?>" alt="user">
        <div class="news_feed_title_content">
            <p><?php echo htmlspecialchars($ROW_USER['fname']) . " " .  htmlspecialchars($ROW_USER['lname']); ?></p>
            <span><?php echo $POST['date'] ?>  <i class="fas fa-globe-americas"></i></span>
        </div>
        <?php 
            if($POST['is_profileimg'])
                {
                    $pronoun = "his";
                    if($ROW_USER['gender'] == "Female")
                    {
                        $pronoun = "her";
                    }
                    echo "<span style='font-weight: normal;color: #aaa;font-size: small;margin: -20px 0px 0px 10px;'>Updated $pronoun profile image</span>";
                }
        ?>
    </div>
    <div class="news_feed_description">
        <?php 
            if (!empty($POST['post'])){
                echo "<p class='news_feed_subtitle'>" . html_entity_decode($POST['post']) . "</p>";
            }
            if (!empty($POST['postimg']) && isset($POST['postimg']) && $POST['has_image'])
            {
                $image = "./uploads/" . $ROW_USER['uid'] . "/" . $POST['postimg'];
                echo "<img src='" . $image . "' alt='image'>"; 
            }
        ?>
    </div>
</div>