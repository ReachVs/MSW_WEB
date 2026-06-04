export default function LandingPage({ onBookService, onExplore }) {
  return (
    <div className="w-full flex-grow">
      {/* Hero Section */}
      <section className="relative min-h-[85vh] py-16 flex flex-col justify-center items-start px-margin architectural-grid overflow-hidden">
        <div className="max-w-screen-2xl mx-auto w-full grid grid-cols-12 gap-gutter items-center">
          <div className="col-span-12 lg:col-span-6 z-10">
            <div className="bg-primary text-on-primary inline-block px-sm py-xs mb-md font-label-sm text-xs uppercase tracking-[0.2em]">
              Performance Lab 01
            </div>
            <h1 className="font-display-xl text-5xl md:text-7xl text-primary leading-none mb-lg select-none">
              PRECISION
              <br />
              <span className="text-secondary">ENGINEERING</span>
            </h1>
            <p className="font-body-lg text-body-lg text-on-surface-variant max-w-md mb-xl">
              Elite motorcycle customisation and clinical technical maintenance
              for the modern purist. We bridge the gap between mechanical soul
              and architectural precision.
            </p>
            <div className="flex gap-md flex-wrap">
              <button
                onClick={onBookService}
                className="bg-secondary text-on-secondary px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-all active:scale-95"
              >
                Book Service
              </button>
              <button
                onClick={onExplore}
                className="border border-primary text-primary px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low transition-all active:scale-95"
              >
                Explore Engineering
              </button>
            </div>
          </div>

          <div className="col-span-12 lg:col-span-6 relative mt-lg lg:mt-0">
            <div className="absolute -top-10 -right-10 w-64 h-64 bg-surface-container-high opacity-50 -z-10"></div>
            <div className="relative group">
              <img
                alt="Premium Motorcycle"
                className="w-full grayscale group-hover:grayscale-0 transition-all duration-700 hairline-border shadow-2xl"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDNGMCOTGGeYbyvsIIreBb0etqPiwSpaut9RIkex2bA2Ho81CbZ100KtNMh5gwLFQVDKWyNo4PhogpjZeI-8TLw25_loi9qEUzTtYe2y5kwd9HECcrGPqLcgf7Z0de-OhQ3CJATEYxW1PyQeq8ZhENVe3fA2SzII7sNzSnAFDjmGS44TyrcmfuoS3VYgmpNdKFOpzJU-NsFa-tjRaHECNpOcTFM3R0PfLwB9a23iFiMxhaw2GgTj5KGX62oXOH-n6Z-vMr1-ke8ciCV"
              />
              <div className="absolute bottom-4 right-4 bg-background/80 backdrop-blur-md p-md hairline-border">
                <span className="font-label-sm text-[10px] block text-on-surface-variant opacity-60">
                  MODEL
                </span>
                <span className="font-headline-md text-headline-md text-primary">
                  APE-01S
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section (Clinical Typography) */}
      <section className="py-xl border-y border-on-surface/5 bg-surface-container-lowest">
        <div className="max-w-screen-2xl mx-auto px-margin grid grid-cols-2 md:grid-cols-4 gap-lg">
          <div className="flex flex-col">
            <span className="font-display-xl text-3xl md:text-5xl text-primary font-bold">
              0.02
              <span className="text-secondary text-xl md:text-2xl font-bold ml-1">
                MM
              </span>
            </span>
            <span className="font-label-sm text-[10px] md:text-xs text-on-surface-variant uppercase mt-xs tracking-widest">
              Tolerance Precision
            </span>
          </div>
          <div className="flex flex-col">
            <span className="font-display-xl text-3xl md:text-5xl text-primary font-bold">
              100
              <span className="text-secondary text-xl md:text-2xl font-bold ml-1">
                %
              </span>
            </span>
            <span className="font-label-sm text-[10px] md:text-xs text-on-surface-variant uppercase mt-xs tracking-widest">
              Data-Driven Logic
            </span>
          </div>
          <div className="flex flex-col">
            <span className="font-display-xl text-3xl md:text-5xl text-primary font-bold">
              24
              <span className="text-secondary text-xl md:text-2xl font-bold ml-1">
                HR
              </span>
            </span>
            <span className="font-label-sm text-[10px] md:text-xs text-on-surface-variant uppercase mt-xs tracking-widest">
              Rapid Prototyping
            </span>
          </div>
          <div className="flex flex-col">
            <span className="font-display-xl text-3xl md:text-5xl text-primary font-bold">
              05
              <span className="text-secondary text-xl md:text-2xl font-bold ml-1">
                AXIS
              </span>
            </span>
            <span className="font-label-sm text-[10px] md:text-xs text-on-surface-variant uppercase mt-xs tracking-widest">
              CNC Fabrication
            </span>
          </div>
        </div>
      </section>

      {/* The Workshop Philosophy */}
      <section className="py-xl px-margin">
        <div className="max-w-screen-2xl mx-auto">
          <div className="mb-xl">
            <h2 className="font-headline-lg text-4xl text-primary uppercase tracking-tight font-black">
              The Workshop Philosophy
            </h2>
            <div className="w-24 h-1 bg-secondary mt-sm"></div>
          </div>

          <div className="grid grid-cols-12 gap-gutter">
            {/* Bento Card 1 */}
            <div className="col-span-12 md:col-span-7 bg-surface-container-low p-lg hairline-border flex flex-col justify-between group hover:bg-primary transition-colors duration-500 min-h-[350px]">
              <div className="flex justify-between items-start">
                <span className="material-symbols-outlined text-secondary text-4xl group-hover:text-on-primary transition-colors">
                  architecture
                </span>
                <span className="font-label-sm text-xs text-on-surface-variant group-hover:text-on-primary/60 transition-colors">
                  01
                </span>
              </div>
              <div className="mt-xl">
                <h3 className="font-headline-md text-2xl text-primary group-hover:text-on-primary transition-colors mb-md uppercase font-bold">
                  Geometric Integrity
                </h3>
                <p className="font-body-md text-body-md text-on-surface-variant group-hover:text-on-primary/80 transition-colors max-w-md">
                  Every component we design follows a strict geometric
                  hierarchy. We strip away the unnecessary to reveal the raw
                  mechanical beauty of the machine, ensuring form perfectly
                  follows function.
                </p>
              </div>
            </div>

            {/* Bento Card 2 */}
            <div className="col-span-12 md:col-span-5 bg-background p-lg hairline-border flex flex-col justify-between group hover:border-secondary transition-all min-h-[350px]">
              <div className="flex justify-between items-start">
                <span className="material-symbols-outlined text-secondary text-4xl">
                  query_stats
                </span>
                <span className="font-label-sm text-xs text-on-surface-variant">
                  02
                </span>
              </div>
              <div className="mt-xl">
                <h3 className="font-headline-md text-2xl text-primary mb-md uppercase font-bold">
                  Data-Driven Diagnostics
                </h3>
                <p className="font-body-md text-body-md text-on-surface-variant">
                  We utilize aerospace-grade sensors and diagnostic arrays to
                  monitor engine health and structural stress in real-time,
                  providing clinical accuracy for every tune.
                </p>
              </div>
            </div>

            {/* Bento Card 3 (Image Focused) */}
            <div className="col-span-12 md:col-span-5 relative overflow-hidden h-[400px] hairline-border group">
              <img
                alt="Workshop Detail"
                className="w-full h-full object-cover grayscale group-hover:scale-105 group-hover:grayscale-0 transition-all duration-700"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXZ7I_6pYXTvHQwCMG3LG_AJwNvg_3eigXEIoy0KBn96kcM3J97yOZPKl97ZCgare8NcJgtFM5JvtvzTnbBa80jfhGsLEG9MBbWXyy8HJi2a6f4KtaQMnoB95FqwFLHzPGdPEgRaRsf3p9ibeCteIHJKWvupGeCvyYh9PN7oDvJkmn49YYdBNY2CBlWYpXihFO2LpkzioZo-eNusRMq2IaXhsBWl9-A6N9gOBpaDsniYXSLShzKeYkKEwn6QbyTEQyt6BEv_LgPJ62"
              />
              <div className="absolute bottom-0 left-0 p-lg bg-background/90 backdrop-blur w-full border-t border-outline-variant">
                <h4 className="font-label-sm text-xs text-primary uppercase font-bold tracking-widest">
                  The Clean Room
                </h4>
              </div>
            </div>

            {/* Bento Card 4 */}
            <div className="col-span-12 md:col-span-7 bg-surface-container p-lg hairline-border flex flex-col justify-center min-h-[400px]">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-md">
                <div className="flex flex-col gap-sm">
                  <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-widest border-b border-outline-variant pb-2">
                    Materials
                  </span>
                  <ul className="font-body-md text-body-md text-on-surface-variant space-y-xs">
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-secondary"></span> T6-6061
                      Aluminum
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-secondary"></span> Grade 5
                      Titanium
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-secondary"></span> Dry
                      Carbon Fiber
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-secondary"></span> 3D
                      Printed Inconel
                    </li>
                  </ul>
                </div>
                <div className="flex flex-col gap-sm">
                  <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-widest border-b border-outline-variant pb-2">
                    Processes
                  </span>
                  <ul className="font-body-md text-body-md text-on-surface-variant space-y-xs">
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-primary"></span> Laser
                      Alignment
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-primary"></span> Stress
                      Analysis
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-primary"></span> Thermal
                      Mapping
                    </li>
                    <li className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 bg-primary"></span> Weight
                      Optimization
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-xl bg-primary text-on-primary px-margin overflow-hidden relative">
        <div className="max-w-screen-2xl mx-auto flex flex-col items-center text-center relative z-10">
          <h2 className="font-display-xl text-3xl md:text-5xl uppercase leading-tight mb-lg font-black tracking-tight select-none">
            Ready to elevate your <br />
            <span className="text-on-primary-container">
              mechanical standard?
            </span>
          </h2>
          <div className="flex flex-col sm:flex-row gap-lg w-full sm:w-auto justify-center">
            <button
              onClick={onBookService}
              className="bg-secondary text-on-secondary px-xl py-md font-label-sm text-xs uppercase tracking-widest hover:bg-white hover:text-primary transition-all active:scale-95"
            >
              Book Service Appointment
            </button>
            <button
              onClick={onExplore}
              className="border border-on-primary text-on-primary px-xl py-md font-label-sm text-xs uppercase tracking-widest hover:bg-on-primary hover:text-primary transition-all active:scale-95"
            >
              Request Technical Specs
            </button>
          </div>
        </div>
        {/* Decorative Element */}
        <div className="absolute -bottom-20 -right-20 text-[120px] md:text-[200px] font-bold text-on-primary/5 select-none pointer-events-none uppercase tracking-tighter font-display-xl">
          MAD APE
        </div>
      </section>

      {/* Footer */}
      <footer className="w-full bg-primary border-t border-secondary/20">
        <div className="flex flex-col items-center justify-center py-lg px-margin gap-md max-w-screen-2xl mx-auto">
          <div className="font-display-xl text-[32px] text-on-primary uppercase tracking-tighter select-none font-bold">
            MAD APE
          </div>
          <div className="flex flex-wrap justify-center gap-lg">
            <a
              className="font-label-sm text-[10px] uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors hover:underline"
              href="#"
            >
              Privacy Policy
            </a>
            <a
              className="font-label-sm text-[10px] uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors hover:underline"
              href="#"
            >
              Terms of Service
            </a>
            <a
              className="font-label-sm text-[10px] uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors hover:underline"
              href="#"
            >
              Technical Documentation
            </a>
            <a
              className="font-label-sm text-[10px] uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors hover:underline"
              href="#"
            >
              Global Support
            </a>
          </div>
          <div className="font-label-sm text-[10px] uppercase tracking-widest text-on-primary/40 text-center mt-md">
            © 2026 MAD APE MOTORWORKS. ENGINEERED TO EXCELLENCE.
          </div>
        </div>
      </footer>
    </div>
  )
}
