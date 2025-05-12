<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <header>
        <h1>Mango</h1>
        <p>Interaktivní <b>správce projektů</b> pro týmy i individuály.</p>
        <?php if (isset($jwtHandler) && $jwtHandler->isLoggedIn()): ?>
            <button onclick="window.location.href='/dashboard'">Začínáme</button>
        <?php else: ?>
            <button onclick="document.querySelector('.nav-action').click()">Začínáme</button>
        <?php endif; ?>
    </header>

    <main class="home-content">
        <div class="landing-text">
            <div>
                <p>Do předmětu <i>Základy webových aplikací</i>.</p>
                <p>Projekt je zaměřen na správu projektů a úkolů. Umožňuje uživatelům vytvářet projekty, přidávat úkoly a spravovat členy projektů.</p>
            </div>
            <img src="public/landing-task.png" alt="Sample task" class="task-image">
        </div>
        <p>
            Vytvořeno pomocí <b>nejnovějších&trade;</b> technologií jako jsou např. PHP.. počkat.. beru zpátky.
            <br>
            (ale vážně, je zázrak že PHP přežilo Y2K jaktože je pořád relevantní)
        </p>
        <p>60% času to funguje na 100% 👍</p>
        <div class="landing-text">
            <img src="public/howard-technology.png" alt="Howard Stark" class="technology-image">
            <div>
                <div style="display: flex; flex-direction: row; justify-content: center; align-items: center;">
                    <div>
                        <p>Backend</p>
                        <ul>
                            <li>PHP</li>
                            <li>MySQL</li>
                            <li>Apache</li>
                        </ul>
                    </div>
                    <div>
                        <p>Frontend</p>
                        <ul>
                            <li>HTML</li>
                            <li>CSS</li>
                            <li>JavaScript</li>
                        </ul>
                    </div>
                    <p>Takový návrat k opicím 🙈</p>
                </div>
                <div>
                    Hostováno pomocí free tieru Oracle VPS (PHP, MySQL).
                    <br>
                    Doménu spravuje Netlify.
                </div>
            </div>
        </div>
    </main>

    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
</body>
</html> 
