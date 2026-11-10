<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="TrackingAid helps disaster response teams coordinate relief inventory, requests, stock releases, and reporting.">
    <title>TrackingAid | Disaster Logistics</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-page">
    <header class="public-header">
        <a class="public-brand" href="{{ route('welcome') }}" aria-label="TrackingAid home">
            <span class="brand-logo-frame"><img class="trackingaid-logo" src="{{ asset('images/trackingaid-logo-transparent.png') }}" alt="TrackingAid"></span>
        </a>
        <nav class="public-nav" aria-label="Main navigation">
            <a href="#capabilities">Capabilities</a>
            <a href="#about">About</a>
            <a class="btn-main public-nav-cta" href="{{ route('login') }}"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Access System</a>
        </nav>
    </header>

    <main>
        <section class="public-hero public-hero--split" aria-labelledby="welcome-title">
            <div class="public-hero-copy">
                <p class="public-eyebrow"><span class="public-eyebrow-dot"></span> Disaster logistics management</p>
                <h1 id="welcome-title">Managing Relief Resources. <span>Supporting Communities.</span></h1>
                <p class="public-intro">A coordinated view of essential supplies, requests, and stock movement helps response teams prepare resources and support communities when they need it most.</p>
                <div class="public-hero-actions">
                    <a class="btn-main public-cta" href="{{ route('login') }}">Access System <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <span class="public-hero-note"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i> Built to support organized response</span>
                </div>
            </div>
            <figure class="public-hero-visual">
                <img src="{{ asset('images/relief-warehouse.svg') }}" alt="Organized relief supplies staged inside a warehouse for distribution">
                <figcaption><span class="hero-caption-icon"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span><span><strong>Ready for coordinated response</strong><small>Inventory, requests, and stock movement in one place</small></span></figcaption>
            </figure>
            <div class="public-hero-orbit public-hero-orbit--one" aria-hidden="true"></div>
            <div class="public-hero-orbit public-hero-orbit--two" aria-hidden="true"></div>
        </section>

        <section class="public-intro-band" id="about" aria-labelledby="about-title">
            <div class="public-section-kicker">Purpose-built for coordination</div>
            <div>
                <h2 id="about-title">A clearer picture of relief operations.</h2>
                <p>TrackingAid brings inventory records, incoming supplies, community requests, releases, and returns into a shared workflow so authorized teams can make informed decisions and maintain accountability.</p>
            </div>
            <div class="public-intro-mark" aria-hidden="true"><i class="fa-solid fa-people-group"></i></div>
        </section>

        <section class="public-offers" id="capabilities" aria-labelledby="offers-title">
            <div class="public-section-heading">
                <div>
                    <p class="public-eyebrow">One coordinated workflow</p>
                    <h2 id="offers-title">What TrackingAid Offers</h2>
                </div>
                <p>Purposeful tools for managing relief resources from arrival through distribution.</p>
            </div>
            <div class="public-capabilities">
                <article class="public-capability public-capability--teal">
                    <span class="public-capability-icon"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span>
                    <h3>Inventory Management</h3>
                    <p>Maintain a clear view of supplies, quantities, categories, and storage locations.</p>
                    <span class="public-card-index">01</span>
                </article>
                <article class="public-capability public-capability--blue">
                    <span class="public-capability-icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span>
                    <h3>Request Management</h3>
                    <p>Review incoming supply requests and follow each request through its status.</p>
                    <span class="public-card-index">02</span>
                </article>
                <article class="public-capability public-capability--amber">
                    <span class="public-capability-icon"><i class="fa-solid fa-truck-ramp-box" aria-hidden="true"></i></span>
                    <h3>Stock Release</h3>
                    <p>Record approved releases and returns with clear movement details.</p>
                    <span class="public-card-index">03</span>
                </article>
                <article class="public-capability public-capability--violet">
                    <span class="public-capability-icon"><i class="fa-solid fa-chart-column" aria-hidden="true"></i></span>
                    <h3>Reports and Monitoring</h3>
                    <p>Review operational records and inventory reports to support planning.</p>
                    <span class="public-card-index">04</span>
                </article>
            </div>
        </section>

        <section class="public-why" aria-label="TrackingAid principles">
            <div class="public-why-heading">
                <p class="public-eyebrow">Built for disaster response</p>
                <h2>Organized information.<br><span>More confident coordination.</span></h2>
            </div>
            <div class="public-why-point"><span><i class="fa-solid fa-list-check" aria-hidden="true"></i></span><div><strong>Clear records</strong><p>Keep essential supply information accessible to authorized teams.</p></div></div>
            <div class="public-why-point"><span><i class="fa-solid fa-route" aria-hidden="true"></i></span><div><strong>Traceable movement</strong><p>Follow stock activity through receipt, release, and return workflows.</p></div></div>
            <div class="public-why-point"><span><i class="fa-solid fa-handshake-angle" aria-hidden="true"></i></span><div><strong>Coordinated support</strong><p>Connect available resources with requests and response activity.</p></div></div>
        </section>
    </main>

    <footer class="public-footer">
        <span class="footer-brand">TrackingAid <span>by ResQOperation</span></span>
        <span>Disaster logistics information system</span>
        <a href="{{ route('login') }}">Access System <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </footer>
</body>
</html>
