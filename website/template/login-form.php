<main>
    <section>
        <header>
            <img class="logo2" src="../res/HeartHands.svg" alt="logo HILFE" />
        </header>
        <form class="form" action="../utils/loginHandler.php" method="POST">
            <ul>
                <li><label for="name">Username</label><input type="text" id="name" name="name" placeholder="Username"></li>
                <li><label for="password">Password</label><input type="password" id="password" name="password" placeholder="Password"></li>
            </ul>
            <div class="options">
                <?php if (isset($_GET["error"])) echo '<p class="error">Impossibile accedere</p>' ?>
                <a href="#" class="dimenticata">Password dimenticata?</a><br />
                <div class="ricordami">
                    <input type="checkbox" id="ricordami" checked name="ricordami" value="Ricordami"><label for="ricordami">Ricordami</label>
                </div>
            </div>
            <footer>
                <input class="log" type="submit" value="Login"></input>
                <p>oppure</p>
                <a class="registrati" href="registrazione.html">Registrati</a>
            </footer>
        </form>
    </section>
</main>