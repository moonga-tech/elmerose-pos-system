<?php 

include 'includes/header.php'; 

if(isset($_SESSION['loggedIn'])) {
    ?>
    <script>
        window.location.href = 'index.php';
    </script>
    <?php
}

?>
<div class="py-5 login-bg">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">

                <?php alertMessage(); ?>
                
                    <div class="card-header bg-transparent border-0 my-2">
                        <div class="text-center">
                            <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                            <h4>Elmerose Management System</h4>
                            <a href="login.php" style="text-decoration: none;">
                                <h5>Admin Login Panel</h5>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        $lockRemaining = 0;
                        if (isset($_SESSION['login_lockout_until']) && time() < $_SESSION['login_lockout_until']) {
                            $lockRemaining = $_SESSION['login_lockout_until'] - time();
                        }
                        $attempts = isset($_SESSION['login_attempts']) ? (int)$_SESSION['login_attempts'] : 0;
                        $maxAttempts = 3;
                        $attemptsLeft = max(0, $maxAttempts - $attempts);
                        ?>
                        <?php if ($attempts > 0 && $lockRemaining == 0): ?>
                            <div class="mb-3">
                                <div class="alert <?= $attemptsLeft <= 1 ? 'alert-danger' : 'alert-warning' ?> py-2" role="alert" style="margin-bottom:0;font-size:0.95rem;">
                                    <strong>Attempts left:</strong> <?= $attemptsLeft; ?> — Enter correct credentials to avoid a 10s lockout.
                                </div>
                            </div>
                        <?php endif; ?>
                        <form action="login-code.php" method="POST" class="admin-login-form" id="adminLoginForm">
                            <div class="mb-3">
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Email Address">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" name="loginBtn" id="loginBtn">Login</button>
                            <div class="text-center mt-3">
                                <a href="forgot-password.php" class="small forgot-password">Forgot Password?</a>
                            </div>
                        </form>
                        <?php if($lockRemaining > 0): ?>
                        <div id="lockOverlay" style="position:absolute; inset:0; background: rgba(255,255,255,0.9); display:flex; align-items:center; justify-content:center;">
                            <div class="text-center">
                                <h5 class="text-danger">Too many failed attempts</h5>
                                <p>Please wait <span id="countdown"><?= $lockRemaining; ?></span>s before trying again.</p>
                            </div>
                        </div>
                        <script>
                            (function(){
                                var remaining = <?= $lockRemaining; ?>;
                                var cd = document.getElementById('countdown');
                                var overlay = document.getElementById('lockOverlay');
                                var form = document.getElementById('adminLoginForm');
                                var loginBtn = document.getElementById('loginBtn');
                                if(form) {
                                    // disable inputs
                                    Array.from(form.querySelectorAll('input, button, a')).forEach(function(el){ el.disabled = true; });
                                }
                                var iv = setInterval(function(){
                                    remaining--;
                                    if(cd) cd.textContent = remaining;
                                    if(remaining <= 0){
                                        clearInterval(iv);
                                        // reload page to clear lockout state from UI (server-side may still have until expired)
                                        window.location.href = window.location.pathname;
                                    }
                                }, 1000);
                            })();
                        </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>