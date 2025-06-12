<footer class="footer">
    <div class="footer-container">
        <div class="footer-logo">
            <img src="chlumeclogo.png" alt="Warriors Logo">
        </div>
        <div class="footer-links">
            <div class="footer-contact">
                <p>Kontakt: 
                    <a href="mailto:info@warriorschlumec.cz">info@warriorschlumec.cz</a> 
                    | Tel: +420 123 456 789
                </p>
            </div>
            <div class="footer-socials">
                <a href="https://www.facebook.com/share/16bLKenfCU/?mibextid=wwXIfr" target="_blank">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://www.instagram.com/warriorschlumec?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank">
                    <i class="fab fa-instagram"></i> Instagram
                </a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Warriors Chlumec. Všechna práva vyhrazena.</p>
        </div>
    </div>
</footer>

<style>
.footer {
    background-color: #000;
    color: #fff;
    padding: 30px 15px 20px;
    text-align: center;
    font-size: 14px;
    flex-shrink: 0;
}

.footer-logo img {
    max-width: 80px;
    opacity: 0.85;
    transition: all 0.3s ease;
}
.footer-logo img:hover {
    opacity: 1;
    transform: scale(1.05);
}

.footer-links {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

.footer-contact a {
    color: #fff;
    text-decoration: none;
}
.footer-contact a:hover {
    color: #d32f2f;
}

.footer-socials {
    display: flex;
    gap: 20px;
}
.footer-socials a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}
.footer-socials a:hover {
    color: #d32f2f;
    transform: translateY(-2px);
}

.footer-bottom {
    margin-top: 20px;
    font-size: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 10px;
}

@media (max-width: 768px) {
    .footer-socials {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>