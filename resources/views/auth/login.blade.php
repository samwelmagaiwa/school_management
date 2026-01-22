@extends('layouts.login_master')

@section('content')
<style>
.login-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Carousel Section with Background Image */
.carousel-section {
    flex: 1;
    position: relative;
    overflow: hidden;
    background-image: url('{{ asset('storage/uploads/landing.jpg') }}');
    background-size: 100% 100%;
    background-position: center center;
    background-repeat: no-repeat;
}

/* Reduced overlay for better image visibility */
.carousel-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.45) 0%, rgba(118, 75, 162, 0.45) 100%);
    z-index: 1;
}

.carousel-wrapper {
    position: relative;
    height: 100%;
    width: 100%;
    z-index: 2;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1.2s ease-in-out;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 4rem;
    color: white;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-content {
    max-width: 650px;
    text-align: center;
    z-index: 2;
    position: relative;
}

/* Floating Icons */
.icon-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2.5rem;
    position: relative;
}

.floating-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    animation: float 3s ease-in-out infinite;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.floating-icon:nth-child(1) {
    animation-delay: 0s;
}

.floating-icon:nth-child(2) {
    animation-delay: 0.5s;
    transform: translateY(-10px);
}

.floating-icon:nth-child(3) {
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.main-icon {
    width: 120px;
    height: 120px;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(15px);
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    margin-bottom: 2rem;
    animation: pulse 2s ease-in-out infinite;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    border: 3px solid rgba(255, 255, 255, 0.4);
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
    }
}

.carousel-title {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
}

.carousel-description {
    font-size: 1.25rem;
    line-height: 1.8;
    opacity: 0.98;
    margin-bottom: 2.5rem;
    text-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
}

.feature-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 2rem;
    text-align: left;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.75rem 1rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateX(5px);
    background: rgba(255, 255, 255, 0.25);
}

.feature-item i {
    font-size: 1.5rem;
}

.feature-item span {
    font-size: 0.95rem;
    font-weight: 500;
}

/* Decorative elements */
.carousel-bg-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
}

.shape-1 {
    width: 500px;
    height: 500px;
    top: -150px;
    right: -150px;
    animation: rotate 20s linear infinite;
}

.shape-2 {
    width: 350px;
    height: 350px;
    bottom: -100px;
    left: -100px;
    animation: rotate 25s linear infinite reverse;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.carousel-indicators {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 12px;
    z-index: 3;
}

.carousel-indicator {
    width: 40px;
    height: 6px;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.carousel-indicator.active {
    background: white;
    width: 60px;
}

/* Login Form Section */
.login-section {
    flex: 0 0 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: white;
    box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
    z-index: 2;
}

.login-form {
    width: 100%;
    max-width: 400px;
}

.login-card {
    box-shadow: none;
    border: none;
}

.login-card .card-body {
    padding: 2rem 0;
}

.brand-logo {
    text-align: center;
    margin-bottom: 2.5rem;
}

.brand-icon {
    width: 75px;
    height: 75px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.2rem;
    margin-bottom: 1rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.login-title {
    font-size: 1.9rem;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: #718096;
    font-size: 1rem;
}

.form-control {
    height: 50px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 0 1.25rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-primary {
    height: 50px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 700;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.form-check-label {
    font-size: 0.9rem;
    color: #4a5568;
    font-weight: 500;
}

.forgot-link {
    color: #667eea;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}

.forgot-link:hover {
    color: #764ba2;
}

/* Responsive */
@media (max-width: 968px) {
    .login-container {
        flex-direction: column;
    }
    
    .carousel-section {
        min-height: 400px;
    }
    
    .login-section {
        flex: 1;
    }
    
    .carousel-title {
        font-size: 2rem;
    }
    
    .floating-icon {
        width: 60px;
        height: 60px;
        font-size: 1.8rem;
    }
    
    .feature-list {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="login-container">
    <!-- Carousel Section with Background Image -->
    <div class="carousel-section">
        <!-- Overlay for better text readability -->
        <div class="carousel-overlay"></div>
        
        <div class="carousel-wrapper">
            <!-- Background Shapes -->
            <div class="carousel-bg-shape shape-1"></div>
            <div class="carousel-bg-shape shape-2"></div>
            
            <!-- Slide 1 - Academic Excellence -->
            <div class="carousel-slide active" data-slide="0">
                <div class="carousel-content">
                    <div class="icon-group">
                        <div class="floating-icon">
                            <i class="icon-book2"></i>
                        </div>
                        <div class="main-icon">
                            <i class="icon-graduation2"></i>
                        </div>
                        <div class="floating-icon">
                            <i class="icon-pen"></i>
                        </div>
                    </div>
                    
                    <h2 class="carousel-title">Transform Academic Excellence</h2>
                    <p class="carousel-description">
                        Empower students, teachers, and administrators with a comprehensive platform designed to elevate learning outcomes.
                    </p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Digital Grade Books</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Smart Attendance</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Progress Tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Report Cards</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 - Student & Parent Engagement -->
            <div class="carousel-slide" data-slide="1">
                <div class="carousel-content">
                    <div class="icon-group">
                        <div class="floating-icon">
                            <i class="icon-users4"></i>
                        </div>
                        <div class="main-icon">
                            <i class="icon-user-tie"></i>
                        </div>
                        <div class="floating-icon">
                            <i class="icon-mobile2"></i>
                        </div>
                    </div>
                    
                    <h2 class="carousel-title">Connect Everyone, Everywhere</h2>
                    <p class="carousel-description">
                        Bridge the gap between school, students, and parents with real-time communication and engagement tools.
                    </p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Parent Portal Access</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Real-time Updates</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Event Notifications</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Student Dashboards</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 3 - Financial Management -->
            <div class="carousel-slide" data-slide="2">
                <div class="carousel-content">
                    <div class="icon-group">
                        <div class="floating-icon">
                            <i class="icon-calculator"></i>
                        </div>
                        <div class="main-icon">
                            <i class="icon-piggy-bank"></i>
                        </div>
                        <div class="floating-icon">
                            <i class="icon-credit-card2"></i>
                        </div>
                    </div>
                    
                    <h2 class="carousel-title">Streamline Financial Operations</h2>
                    <p class="carousel-description">
                        Automate billing, track payments, and manage school finances with complete transparency and accuracy.
                    </p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Fee Management</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Payment Tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Invoice Generation</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Financial Reports</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 4 - Analytics & Insights -->
            <div class="carousel-slide" data-slide="3">
                <div class="carousel-content">
                    <div class="icon-group">
                        <div class="floating-icon">
                            <i class="icon-chart"></i>
                        </div>
                        <div class="main-icon">
                            <i class="icon-graph"></i>
                        </div>
                        <div class="floating-icon">
                            <i class="icon-stats-bars"></i>
                        </div>
                    </div>
                    
                    <h2 class="carousel-title">Data-Driven Decisions</h2>
                    <p class="carousel-description">
                        Unlock powerful insights with comprehensive analytics and visualization tools for smarter school management.
                    </p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Performance Analytics</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Custom Reports</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Visual Dashboards</span>
                        </div>
                        <div class="feature-item">
                            <i class="icon-checkmark-circle"></i>
                            <span>Export & Share</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Indicators -->
            <div class="carousel-indicators">
                <span class="carousel-indicator active" data-slide="0"></span>
                <span class="carousel-indicator" data-slide="1"></span>
                <span class="carousel-indicator" data-slide="2"></span>
                <span class="carousel-indicator" data-slide="3"></span>
            </div>
        </div>
    </div>

    <!-- Login Form Section -->
    <div class="login-section">
        <form class="login-form" method="post" action="{{ route('login') }}">
            @csrf
            <div class="card login-card">
                <div class="card-body">
                    <div class="brand-logo">
                        <div class="brand-icon">
                            <i class="icon-graduation2"></i>
                        </div>
                        <h1 class="login-title">Welcome Back</h1>
                        <p class="login-subtitle">Sign in to continue to your dashboard</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" style="border-radius: 10px; border-left: 4px solid #e53e3e;">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <strong>Oops!</strong> {{ implode(' ', $errors->all()) }}
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-semibold" style="color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem;">Email or Login ID</label>
                        <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="Enter your email or ID" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold" style="color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem;">Password</label>
                        <input name="password" type="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group d-flex align-items-center">
                        <div class="form-check mb-0">
                            <label class="form-check-label">
                                <input type="checkbox" name="remember" class="form-input-styled" {{ old('remember') ? 'checked' : '' }} data-fouc>
                                Remember me
                            </label>
                        </div>

                        <a href="{{ route('password.request') }}" class="ml-auto forgot-link">Forgot password?</a>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            Sign In <i class="icon-arrow-right8 ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.carousel-indicator');
    let currentSlide = 0;
    const slideInterval = 6000; // 6 seconds

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    setInterval(nextSlide, slideInterval);

    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => showSlide(index));
    });
});
</script>
@endsection
