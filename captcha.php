<?php

class Captcha {
    private function generateMathProblem() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operator = rand(0, 1) ? '+' : '-';
        
        switch($operator) {
            case '+':
                $answer = $num1 + $num2;
                break;
            case '-':
                $answer = $num1 - $num2;
                break;
        }
        
        $_SESSION['captcha_answer'] = $answer;
        return "$num1 $operator $num2 = ?";
    }
    
    public function getCaptcha() {
        return $this->generateMathProblem();
    }
    
    public function verifyCaptcha($user_answer) {
        if (isset($_SESSION['captcha_answer'])) {
            $correct_answer = $_SESSION['captcha_answer'];
            unset($_SESSION['captcha_answer']); // One-time use
            return $user_answer === $correct_answer || (string)$user_answer === (string)$correct_answer;
        }
        return false;
    }
}

// Generate new CAPTCHA when requested
if (isset($_GET['generate'])) {
    $captcha = new Captcha();
    echo $captcha->getCaptcha();
    exit();
}
?>