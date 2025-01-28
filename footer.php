<style>
    html, body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
    }

    footer {
        background-color: #2F4F4F; /* darkslategrey */
        color: white;
        padding: 20px 0;
    }

    footer .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    footer p {
        margin: 0;
        font-size: 0.9rem;
    }

    footer ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        gap: 15px;
    }

    footer ul li {
        display: inline;
    }

    footer ul li a {
        color: white;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    footer ul li a:hover {
        color: #FFD700; /* gold */
        text-decoration: underline;
    }

    @media (max-width: 600px) {
        footer .container {
            flex-direction: column;
            text-align: center;
        }
        footer ul {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

</main>

<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> ZX Fleet Partners. All rights reserved.</p>
        <ul>
            <li>
                <a href="about.php">About Us</a>
            </li>
            <li>
                <a href="contact.php">Contact</a>
            </li>
            <li>
                <a href="privacy.php">Privacy Policy</a>
            </li>
        </ul>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const loader = document.getElementById('page-loader');
        if (loader) loader.style.display = 'flex';

        window.addEventListener('load', () => {
            if (loader) loader.style.display = 'none';
        });
    });
</script>
</body>
</html>
