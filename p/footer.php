</main>
    <footer class="terminal-footer">
        <div class="footer-content">
            <p>budget_tracker v1.0.0 | &copy; <?php echo date('Y'); ?> terminal_finance_inc</p>
            <div class="footer-links">
                <a href="privacy.php">privacy</a>
                <a href="terms.php">terms</a>
                <a href="https://github.com/Bhaumik182001/budget-tracker" target="_blank">github</a>
            </div>
        </div>
    </footer>
    <style>
        .terminal-footer {
            background-color: rgba(0, 0, 0, 0.7);
            border-top: 1px solid var(--secondary);
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text);
            backdrop-filter: blur(5px);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .footer-links a {
            color: var(--text);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--accent);
        }
        
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .terminal-header {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
            }
            
            .nav-links {
                gap: 1rem;
            }
        }
    </style>
</body>
</html>