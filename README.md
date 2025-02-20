<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teboho Xaba - Python Developer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #1b3c73, #218838);
            color: white;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            animation: fadeIn 1.5s ease-in;
            overflow-x: hidden;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://source.unsplash.com/random/1920x1080?technology') no-repeat center center/cover;
            opacity: 0.3;
            z-index: -1;
        }
        .navbar {
            display: flex;
            justify-content: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.7);
            animation: slideDown 1s ease-in-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .navbar a:hover {
            color: #90ee90;
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px;
            animation: fadeIn 2s ease-in;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
            margin-right: 20px;
            animation: zoomIn 1.5s ease-in-out;
        }
        @keyframes zoomIn {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .header-text {
            text-align: left;
        }
        .content {
            max-width: 900px;
            margin: 20px auto;
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 12px;
            animation: fadeIn 1.5s ease-in;
        }
        .projects {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .project-card {
            background: #2a4d69;
            padding: 15px;
            border-radius: 10px;
            width: 250px;
            text-align: center;
            transform: scale(0.8);
            opacity: 0;
            animation: scaleUp 1s ease-in-out forwards;
        }
        @keyframes scaleUp {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        a {
            color: #90ee90;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .video-container {
            margin: 20px auto;
            width: 100%;
            max-width: 560px;
        }
        iframe {
            width: 100%;
            height: 315px;
            border-radius: 10px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #1b3c73;
        }
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            .profile-img {
                margin: 0 auto 10px;
            }
            .header-text {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="background-animation"></div>
    <div class="navbar">
        <a href="#about">About</a>
        <a href="#projects">Projects</a>
        <a href="#youtube">YouTube</a>
        <a href="#contact">Contact</a>
    </div>
    <div class="header-container">
        <img src="https://raw.githubusercontent.com/TebohoXaba/My-Profile/main/.github/Title.jpg" alt="Teboho Xaba" class="profile-img">
        <div class="header-text">
            <h1>🚀 Teboho Xaba - The Value Chain Programmer</h1>
            <h3>Python Developer | Data Science | Automation | Machine Learning | Supply Chain | Logistics</h3>
        </div>
    </div>
    <div class="content" id="about">
        <h2>About Me</h2>
        <p>👋 Hi there! I'm <strong>Teboho Xaba</strong>, a Python developer passionate about <strong>data analytics, automation, and machine learning</strong>.</p>
        <p>With a background in <strong>Logistics and Supply Chain Management</strong>, specializing in <strong>Road Transport</strong>, I strive to leverage <strong>technology for efficiency and innovation</strong>.</p>
        <p>🔹 <em>Looking to collaborate?</em> Feel free to explore my projects or get in touch!</p>
    </div>
    <div class="content" id="projects">
        <h2>🚀 Featured Projects</h2>
        <div class="projects">
            <div class="project-card"><a href="https://zxfleet.co.za">Z X Fleet</a><br>3P - Road Freight Logistics Partner site</div>
            <div class="project-card"><a href="https://gcswarehouse.co.za">GCS Warehouse</a><br>Smart Home Improvements E-Commerce site</div>
            <div class="project-card"><a href="#">Dear Diary</a><br>Personal journal & AI insights App (*Coming Soon!*)</div>
        </div>
    </div>
    <div class="content" id="contact">
        <h2>📬 Get In Touch</h2>
        <p>
            <a href="https://www.linkedin.com/in/teboho-xaba-a142b617b/">LinkedIn</a> |
            <a href="https://github.com/TebohoXaba">GitHub</a> |
            <a href="https://www.youtube.com/@Real_Nonkosi">YouTube</a> |
            <a href="mailto:teboho.xaba@zxfleet.co.za">Email</a>
        </p>
    </div>
