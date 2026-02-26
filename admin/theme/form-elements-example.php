<?php
/**
 * Form Template - Basic Form Elements
 * Star Admin 2 Inspired Design
 */

// Page configuration
$page_title = 'Forms - Basic Elements';
$page_header = [
    'title' => 'Basic Form Elements',
    'subtitle' => 'Create and manage forms with professional styling'
];

$breadcrumb = [
    ['text' => 'Forms', 'url' => '#', 'active' => false],
    ['text' => 'Basic Elements', 'url' => '#', 'active' => true]
];

ob_start();
?>

<!-- Form Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Default Form -->
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Default Form</h5>
                    <p class="text-muted mt-2">Basic form layout with standard styling</p>
                </div>
                <div class="card-body">
                    <form class="form-default">
                        <div class="form-group">
                            <label for="username" class="form-label required">Username</label>
                            <input type="text" class="form-control" id="username" placeholder="Enter your username" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter your email" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label required">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label required">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" placeholder="Confirm password" data-required>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" checked>
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="btn-group mt-20">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="check"></i> Submit
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i data-feather="x"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Horizontal Form -->
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Horizontal Form</h5>
                    <p class="text-muted mt-2">Two-column form layout</p>
                </div>
                <div class="card-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <div>
                                <label for="h_email" class="form-label required">Email</label>
                                <input type="email" class="form-control" id="h_email" placeholder="Enter email" data-required>
                            </div>
                            <div>
                                <label for="h_mobile" class="form-label required">Mobile</label>
                                <input type="tel" class="form-control" id="h_mobile" placeholder="Enter mobile number" data-required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div>
                                <label for="h_password" class="form-label required">Password</label>
                                <input type="password" class="form-control" id="h_password" placeholder="Enter password" data-required>
                            </div>
                            <div>
                                <label for="h_re_password" class="form-label required">Re Password</label>
                                <input type="password" class="form-control" id="h_re_password" placeholder="Confirm password" data-required>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="h_remember" checked>
                            <label class="form-check-label" for="h_remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="btn-group mt-20">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="check"></i> Submit
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i data-feather="x"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Input Sizes -->
    <div class="row mt-24">
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Input Sizes</h5>
                    <p class="text-muted mt-2">Add classes like <code>.form-control-lg</code> and <code>.form-control-sm</code></p>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Large input</label>
                        <input type="text" class="form-control form-control-lg" placeholder="Large input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default input</label>
                        <input type="text" class="form-control" placeholder="Default input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Small input</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Small input">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Select Sizes -->
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Select Sizes</h5>
                    <p class="text-muted mt-2">Add classes like <code>.form-select-lg</code> and <code>.form-select-sm</code></p>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Large select</label>
                        <select class="form-select form-control-lg">
                            <option selected>Select option</option>
                            <option value="1">Option 1</option>
                            <option value="2">Option 2</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default select</label>
                        <select class="form-select">
                            <option selected>Select option</option>
                            <option value="1">Option 1</option>
                            <option value="2">Option 2</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Small select</label>
                        <select class="form-select form-control-sm">
                            <option selected>Select option</option>
                            <option value="1">Option 1</option>
                            <option value="2">Option 2</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Checkbox and Radio Controls -->
    <div class="row mt-24">
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Checkbox Controls</h5>
                    <p class="text-muted mt-2">Default appearance in primary color</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check_default">
                                <label class="form-check-label" for="check_default">Default</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check_checked" checked>
                                <label class="form-check-label" for="check_checked">Checked</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check_disabled" disabled>
                                <label class="form-check-label" for="check_disabled">Disabled</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check_disabled_checked" checked disabled>
                                <label class="form-check-label" for="check_disabled_checked">Disabled Checked</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radio_default" name="demo_radio">
                                <label class="form-check-label" for="radio_default">Default</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radio_selected" name="demo_radio" checked>
                                <label class="form-check-label" for="radio_selected">Selected</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radio_disabled" name="demo_radio2" disabled>
                                <label class="form-check-label" for="radio_disabled">Disabled</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radio_disabled_selected" name="demo_radio2" checked disabled>
                                <label class="form-check-label" for="radio_disabled_selected">Disabled Selected</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Basic Input Groups -->
        <div class="col-lg-6 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Input Groups</h5>
                    <p class="text-muted mt-2">Bootstrap input groups styling</p>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Email input</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="email" class="form-control" placeholder="example">
                            <span class="input-group-text">.com</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search...">
                            <button class="btn btn-primary" type="button">
                                <i data-feather="search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Two Column Form -->
    <div class="row mt-24">
        <div class="col-lg-12 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Personal Information Form</h5>
                    <p class="text-muted mt-2">Multi-field form with grouped sections</p>
                </div>
                <div class="card-body">
                    <form class="form-horizontal">
                        <!-- Personal Info -->
                        <div class="mb-20">
                            <h6 class="mb-15" style="font-weight: 600; color: var(--text-primary);">Personal Information</h6>
                            
                            <div class="form-group">
                                <div>
                                    <label for="first_name" class="form-label required">First Name</label>
                                    <input type="text" class="form-control" id="first_name" placeholder="First name" data-required>
                                </div>
                                <div>
                                    <label for="last_name" class="form-label required">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" placeholder="Last name" data-required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div>
                                    <label for="gender" class="form-label required">Gender</label>
                                    <select class="form-select" id="gender" data-required>
                                        <option selected>Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="dob" class="form-label required">Date of Birth</label>
                                    <input type="date" class="form-control" id="dob" data-required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Section -->
                        <div class="mb-20">
                            <h6 class="mb-15" style="font-weight: 600; color: var(--text-primary);">Address</h6>
                            
                            <div class="form-group full-width">
                                <label for="address1" class="form-label required">Address Line 1</label>
                                <input type="text" class="form-control" id="address1" placeholder="Street address" data-required>
                            </div>
                            
                            <div class="form-group">
                                <div>
                                    <label for="city" class="form-label required">City</label>
                                    <input type="text" class="form-control" id="city" placeholder="City" data-required>
                                </div>
                                <div>
                                    <label for="state" class="form-label required">State</label>
                                    <input type="text" class="form-control" id="state" placeholder="State" data-required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div>
                                    <label for="postcode" class="form-label required">Postal Code</label>
                                    <input type="text" class="form-control" id="postcode" placeholder="Postal code" data-required>
                                </div>
                                <div>
                                    <label for="country" class="form-label required">Country</label>
                                    <input type="text" class="form-control" id="country" placeholder="Country" data-required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group mt-20">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="check"></i> Submit
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i data-feather="x"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    code {
        background-color: var(--bg-light);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        color: var(--color-danger);
    }
    
    h6 {
        font-size: 15px;
    }
</style>
<?php
$page_content = ob_get_clean();
include __DIR__ . '/base-layout.php';
?>
