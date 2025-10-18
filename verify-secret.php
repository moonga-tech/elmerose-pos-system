<?php
require_once 'config/function.php';
require_once 'config/dbcon.php';

// Expect ?email=...&role=admin|customer
$email = isset($_GET['email']) ? validated($_GET['email']) : (isset($_POST['email']) ? validated($_POST['email']) : '');
$role = isset($_GET['role']) ? validated($_GET['role']) : (isset($_POST['role']) ? validated($_POST['role']) : 'admin');

if ($email === '') {
    redirect('forgot-password.php', 'Email not provided');
}

$table = $role === 'customer' ? 'customers' : 'admins';
$stmt = $conn->prepare("SELECT secret_question FROM $table WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secret_question = $row['secret_question'] ?? '';
} else {
    redirect('forgot-password.php', 'Email not found');
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify secret question</title>
    <link href="assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .center-card {
            max-width: 480px;
            margin: 6vh auto;
        }

        .question {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="center-card">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php alertMessage(); ?>
                    <h4 class="card-title mb-3">Verify your account</h4>
                    <p class="mb-2 text-muted small">Please answer the secret question for
                        <strong><?php echo htmlspecialchars($email); ?></strong></p>

                    <form method="post" action="forgot-password-code.php" novalidate>
                        <input type="hidden" name="action" value="verifySecret">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

                        <div class="mb-3">
                            <label class="form-label question">Secret Question</label>
                            <div class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($secret_question)); ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="secret_answer" class="form-label">Your Answer</label>
                            <input id="secret_answer" class="form-control" type="text" name="secret_answer" required
                                autofocus>
                            <div class="invalid-feedback">Please provide your secret answer.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="forgot-password.php" class="text-decoration-none mb-3 d-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                                </svg>
                                Back
                            </a>
                            <button class="btn btn-primary" type="submit">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // simple client-side validation
        (function() {
            const form = document.forms[0];
            form.addEventListener('submit', function(e) {
                const ans = form.secret_answer.value.trim();
                if (!ans) {
                    e.preventDefault();
                    form.secret_answer.classList.add('is-invalid');
                    form.secret_answer.focus();
                }
            });
        })();
    </script>
</body>

</html>
