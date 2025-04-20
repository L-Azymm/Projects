<?php
$header_title = "About Us";
$header_icon = "assets/image/about-icon.png";
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Swiftride</title>

    <style>
        /* General body styling */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2efeb;
        }



        /* About Us Section */
        .about-section {
            padding: 20px;
        }

        .about-header {
            background-color: #A7C7E7;
            padding: 10px;
            display: flex;
            align-items: center;
            border: 2px solid black;
        }

        .about-header h2 {
            font-size: 20px;
            margin: 0;
            color: black;
        }

        .team-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            /* Allow wrapping for smaller screens */
            margin-top: 20px;
        }

        .team-member {
            background-color: white;
            border: 2px solid black;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin: 10px;
            width: 23%;
            /* Adjust width as needed */
            box-sizing: border-box;
            text-align: center;
        }

        .member-name {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .member-role {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }

        .member-description {
            font-size: 14px;
            color: #444;
        }
    </style>
</head>

<body>

    <section class="about-section">
        <div class="about-header">
            <h2>Our Mission</h2>
        </div>

        <div>
            <p>Swiftride, GMI Bus Ticketing System aims to provide an easy and efficient way for GMI student and staff to book transportation for various events and trips.</p>
        </div>

    </section>

    <section class="about-section">
        <div class="about-header">
            <h2>Meet Our Team</h2>
        </div>
        <div class="team-container">
            <div class="team-member">
                <div class="member-name">Luqman Aziem</div>
                <div class="member-role">Project Manager</div>
                <div class="member-description">Luqman is responsible for overseeing the project and ensuring everything
                    runs smoothly.</div>
            </div>
            <div class="team-member">
                <div class="member-name">Wan Khairulnaim</div>
                <div class="member-role">UI/UX Designer</div>
                <div class="member-description">Wan designs the user interface and ensures a seamless user
                    experience for all our users.</div>
            </div>
            <div class="team-member">
                <div class="member-name">Azris De Zaini</div>
                <div class="member-role">Quality Assurance</div>
                <div class="member-description">Azris ensures that the system is bug-free and meets the highest
                    quality standards.</div>
            </div>
            <div class="team-member">
                <div class="member-name">Adam Riezqie</div>
                <div class="member-role">Lead Developer</div>
                <div class="member-description">Adam is the technical wizard behind the code, creating innovative
                    solutions for this system.</div>
            </div>
        </div>
    </section>


</body>

</html>