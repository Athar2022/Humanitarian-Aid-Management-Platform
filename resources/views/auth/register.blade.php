<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - منصة المساعدات الإنسانية</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .success {
            color: #27ae60;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إنشاء حساب جديد</h1>
        
        <form id="registerForm">
            <div class="form-group">
                <label for="name">الاسم الكامل</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">تأكيد كلمة المرور</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            
            <div class="form-group">
                <label for="role">نوع المستخدم</label>
                <select id="role" name="role" required>
                    <option value="">اختر نوع المستخدم</option>
                    <option value="beneficiary">مستفيد</option>
                    <option value="volunteer">متطوع</option>
                    <option value="admin">مدير</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="phone">رقم الهاتف (اختياري)</label>
                <input type="tel" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="address">العنوان (اختياري)</label>
                <input type="text" id="address" name="address">
            </div>
            
            <button type="submit">إنشاء الحساب</button>
        </form>
        
        <div class="login-link">
            <p>لديك حساب بالفعل؟ <a href="/login">تسجيل الدخول</a></p>
        </div>
        
        <div id="message"></div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // التحقق من تطابق كلمة المرور
            if (data.password !== data.password_confirmation) {
                showMessage('كلمة المرور وتأكيدها غير متطابقين', 'error');
                return;
            }
            
            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    showMessage('تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.', 'success');
                    // حفظ التوكن في localStorage
                    localStorage.setItem('auth_token', result.token);
                    localStorage.setItem('user', JSON.stringify(result.user));
                    // إعادة توجيه إلى لوحة التحكم
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                } else {
                    if (result.errors) {
                        let errorMessage = '';
                        for (const field in result.errors) {
                            errorMessage += result.errors[field].join(', ') + '<br>';
                        }
                        showMessage(errorMessage, 'error');
                    } else {
                        showMessage(result.message || 'حدث خطأ أثناء إنشاء الحساب', 'error');
                    }
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال بالخادم', 'error');
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = message;
            messageDiv.className = type;
        }
    </script>
</body>
</html>
