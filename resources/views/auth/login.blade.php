@extends('layouts.login_master')

@section('content')
<style>
.login-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Carousel Section */
.carousel-section {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.carousel-wrapper {
    position: relative;
    height: 100%;
    width: 100%;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1s ease-in-out;
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
    max-width: 600px;
    text-align: center;
    z-index: 2;
}

.carousel-icon {
    font-size: 5rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.carousel-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.carousel-description {
    font-size: 1.2rem;
    line-height: 1.6;
    opacity: 0.95;
    margin-bottom: 2rem;
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
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-indicator.active {
    background: white;
    transform: scale(1.2);
}

/* Decorative elements */
.carousel-bg-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.shape-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
}

.shape-2 {
    width: 300px;
    height: 300px;
    bottom: -80px;
    left: -80px;
}

/* Login Form Section */
.login-section {
    flex: 0 0 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: white;
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
    margin-bottom: 2rem;
}

.brand-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin-bottom: 1rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: #718096;
    font-size: 0.95rem;
}

.form-control {
    height: 48px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    padding: 0 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-primary {
    height: 48px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    font-size: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.form-check-label {
    font-size: 0.9rem;
    color: #4a5568;
}

.forgot-link {
    color: #667eea;
    font-weight: 500;
    font-size: 0.9rem;
    text-decoration: none;
}

.forgot-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 968px) {
    .login-container {
        flex-direction: column;
    }
    
    .carousel-section {
        min-height: 350px;
    }
    
    .login-section {
        flex: 1;
    }
    
    .carousel-title {
        font-size: 2rem;
    }
    
    .carousel-description {
        font-size: 1rem;
    }
}
</style>

<div class="login-container">
    <!-- Carousel Section -->
    <div class="carousel-section">
        <div class="carousel-wrapper">
            <!-- Background Shapes -->
            <div class="carousel-bg-shape shape-1"></div>
            <div class="carousel-bg-shape shape-2"></div>
            
            <!-- Slide 1 -->
            <div class="carousel-slide active" data-slide="0">
                <div class="carousel-content">
                    <div class="carousel-icon">
                        <i class="icon-graduation2"></i>
                    </div>
                    <h2 class="carousel-title">Comprehensive Student Management</h2>
                    <p class="carousel-description">
                        Streamline admissions, track academic progress, manage attendance, and generate detailed reports—all in one powerful platform.
                    </p>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="carousel-slide" data-slide="1">
                <div class="carousel-content">
                    <div class="carousel-icon">
                        <i class="icon-users4"></i>
                    </div>
                    <h2 class="carousel-title">Engage Parents & Students</h2>
                    <p class="carousel-description">
                        Keep parents informed with real-time access to grades, attendance, and school announcements through dedicated portals.
                    </p>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="carousel-slide" data-slide="2">
                <div class="carousel-content">
                    <div class="carousel-icon">
                        <i class="icon-cash3"></i>
                    </div>
                    <h2 class="carousel-title">Smart Financial Management</h2>
                    <p class="carousel-description">
                        Automate fee collection, track payments, generate invoices, and manage school finances with complete transparency and accuracy.
                    </p>
                </div>
            </div>
            
            <!-- Slide 4 -->
            <div class="carousel-slide" data-slide="3">
                <div class="carousel-content">
                    <div class="carousel-icon">
                        <i class="icon-stats-bars"></i>
                    </div>
                    <h2 class="carousel-title">Powerful Analytics & Insights</h2>
                    <p class="carousel-description">
                        Make data-driven decisions with comprehensive dashboards, performance analytics, and customizable reports for every stakeholder.
                    </p>
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
                        <p class="login-subtitle">Sign in to access your dashboard</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" style="border-radius: 8px;">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <strong>Oops!</strong> {{ implode(' ', $errors->all()) }}
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-semibold" style="color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem;">Email or ID</label>
                        <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="Enter your email or login ID" required>
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
    const slideInterval = 5000; // 5 seconds

    function showSlide(index) {
        // Remove active class from all
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    // Auto advance slides
    setInterval(nextSlide, slideInterval);

    // Click on indicators
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => showSlide(index));
    });
});
</script>
@endsection
