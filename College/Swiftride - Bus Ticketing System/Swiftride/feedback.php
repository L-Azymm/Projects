<?php
$header_title = "Feedback";
$header_icon = "assets/image/feedback-icon.png";
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback | Swiftride</title>
    <link rel="icon" href="favicon.png" type="image/x-icon">

    <style>
        /* General body styling */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2efeb;
        }

        /* Feedback section styling */
        section {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        /* Feedback form container */
        .feedback-container {
            width: 100%;
            max-width: 600px;
            background-color: white;
            border: 2px solid black;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }

        .feedback-container h2 {
            margin-top: 0;
            padding: 10px;
            border: 2px solid black; /* Adds a blue border */
            color: black; /* Changes the text color to blue */
            background-color: #A7C7E7; /* Optional: Add a background color for contrast */
            text-align: center; /* Optional: Center the text */
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
        }

        .input-group textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #000000;
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: #A7C7E7;
            color: black;
            padding: 10px 15px;
            border: 2px solid black;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #8ebbe9;
        }

        /* Reviews section styling */
        .reviews-container {
            width: 100%;
            max-width: 600px;
            background-color: white;
            border: 2px solid black;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }

        .reviews-container h2 {
            margin-top: 0;
            padding: 10px;
            border: 2px solid black; /* Adds a blue border */
            color: black; /* Changes the text color to blue */
            background-color: #A7C7E7; /* Optional: Add a background color for contrast */
            text-align: center; /* Optional: Center the text */
        }

        .review {
            background-color: white;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <section>
        <div class="feedback-container">
            <h2>Submit Your Feedback</h2>
            <form id="feedbackForm">
                <div class="input-group">
                    <label for="feedback">Your Feedback:</label>
                    <textarea id="feedback" name="feedback" placeholder="Write your feedback here..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Feedback</button>
            </form>
        </div>
    </section>

    <!-- Reviews Section -->
    <section>
        <div class="reviews-container">
            <h2>Customer Reviews</h2>

            <div id="reviews">
                <div class="review">
                    <p><strong>Amir:</strong> The service was amazing and fast!</p>
                </div>
                <div class="review">
                    <p><strong>Abdul:</strong> I love the user interface, very easy to navigate.</p>
                </div>
                <div class="review">
                    <p><strong>Zane:</strong> Customer support was very helpful.</p>
                </div>
                <div class="review">
                    <p><strong>Raju:</strong> Could use some improvements in speed, but overall good experience.</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('feedbackForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from refreshing the page

            // Get the feedback text from the textarea
            const feedbackText = document.getElementById('feedback').value;

            if (feedbackText.trim() === "") {
                alert("Please enter some feedback before submitting.");
                return;
            }

            // Create a new review div
            const newReview = document.createElement('div');
            newReview.classList.add('review');
            newReview.innerHTML = `<p><strong>You:</strong> ${feedbackText}</p>`;

            // Add the new review to the reviews container
            document.getElementById('reviews').appendChild(newReview);

            // Clear the feedback textarea after submission
            document.getElementById('feedback').value = "";
        });
    </script>
</body>

</html>
