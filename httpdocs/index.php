<!DOCTYPE html>
<html lang="en">
  <head>
    <title>VKBLN Vocabulary Learning</title>
    <meta charset="UTF-8">
    <meta name="description" content="German - English vocabulary trainer.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="styles.css">

    <?php

      // Database configuration
      $servername = "localhost:3306";
      $username = "username";
      $password = "password";
      $dbname = "dbname";

      // Establish connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      $conn->set_charset("utf8mb4");
      if ($conn->connect_error)
      {
        die("Connection failed: " . $conn->connect_error);
      }

      // TODO rename this.
      $language = "deu";
      if (isset($_GET["mode"]) && $_GET["mode"] == "en_de")
      {
        $mode = "en_de";
        $language = "deu";
      }
      else
      {
        $mode = "de_en";
        $language = "eng";
      }

      // Fetch a random word from the DB.
      $sql = "SELECT id, word FROM vocabulary where language='$language' ORDER BY RAND() LIMIT 1";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $word_id = $row["id"];
      $word = $row["word"];

      // Result to display to the user ("correct" or "not correct").
      // TODO rename this.
      $message = "";

      // Check if the form has been submitted, indicating that the user has attempted to translate a word.
      if ($_SERVER["REQUEST_METHOD"] == "POST")
      {
        // Get the user's answer from the form.
        $user_answer = $_POST["answer"];
        $word_id_previous = $_POST["word_id"];

        // Load possible translations from the DB.
        // TODO handle synonyms.
        $sql = "select * from vocabulary where id in(select translation from translation where original = '$word_id_previous') LIMIT 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $correct_answer = $row["word"];

        // Check the given answer and prepare a message.
        if ($user_answer == $correct_answer)
        {
          $message = "Correct!";
        }
        else
        {
          if (strtolower($user_answer) === strtolower($correct_answer))
          {
            $message = "Correct! But watch your capitalization, it should be: " . $correct_answer;
          }
          else
          {
            $message = "Wrong! The correct answer is: " . $correct_answer;
          }
        }

        // Fetch a new random word and translation from the vocabulary table.
        // TODO duplicate code/query
        $sql = "SELECT id, word FROM vocabulary where language='$language' ORDER BY RAND() LIMIT 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $word_id = $row["id"];
        $word = $row["word"];
      }

      // Close the database connection.
      $conn->close();
    ?>
  </head>

  <body>

    <h1>VKBLN</h1>

    <p class="message">Fortify your core German vocabulary. Learn the most commonly used German words and get results rapidly.</p>
    <p class="message">Note that many words can have multiple meanings - focus on the most common one and watch your <a href="https://www.thegermanproject.com/german-lessons/nouns">capitalization rules</a>!</p>
    <h2><?php echo $word; ?></h2>

    <form method="post">
      <label for="answer">Translation:</label>
      <input type="text" name="answer" autofocus="">
      <input type="hidden" name="word_id" value="<?php echo $word_id; ?>">
      <input type="submit" value="Check" title="Or just hit enter!">
    </form>

    <h3 class="message"><?php echo $message; ?></h3>
    <p class="buttons_bottom">
      <button onclick="toggleMode()"><?php echo $mode == "de_en" ? "Switch to English -> German" : "Switch to German -> English"; ?></button>
      <button onclick="location.reload()">Next word</button>
    </p>

    <script>
      function toggleMode() {
        var current_mode = "<?php echo $mode; ?>";
        var new_mode = current_mode == "de_en" ? "en_de" : "de_en";
        var url = window.location.href.replace(/\?.*$/, '');
        window.location.href = url + "?mode=" + new_mode;
      }
    </script>
  </body>

  <footer>
    <a class="footer_a" href="privacy.html">Privacy policy</a>
    <a class="footer_a" href="disclaimer.html">Disclaimer</a>
  </footer>
</html>
