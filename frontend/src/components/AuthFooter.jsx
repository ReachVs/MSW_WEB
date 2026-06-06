const footerLinks = [
  'Privacy Policy',
  'Terms of Service',
  'Technical Documentation',
  'Global Support',
]

export default function AuthFooter() {
  return (
    <footer className="w-full mt-auto bg-primary border-t border-secondary/20 flex flex-col items-center justify-center py-lg px-margin gap-md">
      <div className="font-headline-lg text-3xl text-on-primary uppercase tracking-tighter">
        MAD APE
      </div>
      <div className="flex gap-lg flex-wrap justify-center">
        {footerLinks.map((item) => (
          <span
            key={item}
            className="font-label-sm text-xs uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors"
          >
            {item}
          </span>
        ))}
      </div>
      <p className="font-label-sm text-xs uppercase tracking-widest text-on-primary/40 text-center">
        2026 MAD APE MOTORWORKS. ENGINEERED TO EXCELLENCE.
      </p>
    </footer>
  )
}
