import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  Activity,
  AlertCircle,
  ArrowLeft,
  ArrowRight,
  BarChart3,
  Bike,
  CalendarDays,
  CalendarPlus,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  CircleGauge,
  Cog,
  Cpu,
  Factory,
  Gauge,
  Hammer,
  LayoutDashboard,
  LogIn,
  LogOut,
  PackageCheck,
  RefreshCw,
  Search,
  Settings,
  ShieldCheck,
  Wrench,
} from 'lucide-react'
import { useMemo } from 'react'
import { useForm } from 'react-hook-form'
import {
  BrowserRouter,
  Link,
  NavLink,
  Navigate,
  Outlet,
  Route,
  Routes,
} from 'react-router-dom'
import { z } from 'zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api, csrf } from '../services/api'

const images = {
  hero: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB55Kqhg5hA76ojOKy3WDbODwaeBt8DddkM_MMwN_4Tkkm57sPHeCfV4yPWmS2fQAt6gSfklLI1nkZWR-vEJOCW1zD3imPh41SNqmCt8Q1fhrLLTQQfV9WM8wb9aROzyPpQwCOND_CNGI5zt9qPloJ7OBlByVeD3RGCmlNBu5s0aRORS0gT9Lr3_Gue7GZWwrAxs1L_HD_PnOQbrkH5SjeGVayh0cCmAX92Aik38LK00ms0DruUwaW4fA6WvGXKUwPQ3F6UQWr3oFuW',
  engine:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAZiFU7Zm1CuCUnL6ZQAmiH_UPqc1EeOjQPA3w2wKew1kTHp-7vsK1U7W_v-Iw7zii29Zjd4M04HLcfFsL374zCbHV3AJ-eiRXtKOp-0NOH-a6PCegyme6HGunbqhb909I2tH3VuOi_ITL7rpNGC8xMBpD8lrF88CV7YENbDTx24hViA9gUWiPtVQ2XcpLkr3y9BKVd1TvIMqbtD2ye44gjJbPo7nZPcMxZ5AU3jI6wXGn2ForY5O-_T7o9SUYZHVQukcBeOm27rEJY',
  helmet:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBg71uq53ifV7z-SMlPQB5Qt_Tca_lo1jH1AIHXfBk3M5hRh_NUcRMC0tf36pZDpTP24zNvi-qlu5aY6U33fy5XBtpbGD7lcjE5yZSHFOV5468FSEEO0hpEQVvUj7ephuL1bLmpEnnlchEBVinq1d5wn7dmghTevXVeFlHZ0L-NTD9qGmmDYms7kScMdbn3j6-HsBdf2h3uFSt4eTzEcdnD0mM6Uc-xKO-lUf5q0nwx7JVbfs5OgoO7xRNibBtBOBg-3lrlpREw3h0E',
  workshop:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuDF115Z2gLe2lq8qKLjCiWZmSlK5LCDCLlZPxaLmD-gsz560U7P8aAdCSSHJ5fq3x9jB481abafuN2Hgpz4tIk8cSjShChtWIgTRYHtkvvGzLJrKiBu6ESKj0D1XlA2lUEfJnZQpAjkX8udsiHuzPSuXSIyXfRmRPeOSapXG6Wy5OknygWGPp1hJvw5rLifV4eH05S9Ilrzfz9q8SKQYz20UdHkXtokP1XO3p-fQXZ100akBCuYPaNjLlHWpRLuxYdi2tDIE8utKrq8',
  serviceHero:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCnDWs12fAjshRPN0ONA4fHP9hP-RGYI3wi7cXvucFURNwlNrjPDdUQsU5D6Za995UZsPbBwPOxHApCDoEIxPXaCugbAJzMoM70sLhvAy0Uy4dhsQHl-ySG-Kb0rsJLMlZeHuiJUwz6WwrJOWY2xIGR0HzDmsquUgr2YZ32xzmK99RLw2ZVUT_Uial3VVAjKsJnbjYYsAHzHXGx4eXojszDAE-svoxumgtDAFCPgAHekgQL3R8qTgWlcsYjsxFSs8_9E2BWB2cMKcy_',
  rebuild:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuDAGfHI9DAE0td8hYJHXOjQGutADgctOvP3EJAQ42wI8Fq8a-jSiKHwZzWgIwUnTsE10bWDt6fc7uNZL_URCA68WR324nrw_vdEt1SvVrTODVrioq1L1thvTG1yXR6-be_sAe8AOOJ33G6h8PzwkncizQ6ME8KVYGzxGKaWKB-LvmEyQTbT2-yiJ3yKwWys_YSHyhg7k6bj1UUEaY-93P92je_5h6POa4_-iACRoSBfY-Qk9C1Cei9_sGtACyj9I0iQfbPLQqtlGPa8',
  chain:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuA_dD89aCiXikWoWTd5OHx-LE_uugVJj4vB8dbwECLQFuslbBrsC14lvZpYlbZmktORvBPBbk5Le1OsRsFSsmApZQWrcmtdLF2Z2X7UnOjo8X2qJyjOlx_6ltw9T1BGlHmlMsniJdOpU3HGAoa2oA8gZR2kxwMRODd7UMh98YKEHqwb0xTsDZStmBf8gVlQKjXtZltFHRHP42omYy2zPxgFC1oiuFrw4GAD_-AOwSZ2kvl_FuPGzurGvOtaqxTq7I9fIIedDA0W2k6y',
  booking:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCWx8t7g0co9Q2RVubf0M6wvGYO4FDWce8BJTWXOicOT5HI0tfUkFy7TPjOP_6BnO1wHmN4K-I6lQns3dzV3PAxhZTPaIw2itAAUQtkG5zbJp2LU0-dKdN_TEwJzldc3UduaPVLJr-hD1s-NJSHAPZNLkOMAGiOOQqke7vLxSOJsh2US7iXlUYog1hQ8LQsCWGBAd3mOYAusW8Q2wjqU_xR4Ie1AhgfAEpmCwsJdFYBb8uGmTKHVrk9hZK2wkjMauydT2e5nrfvVmLf',
  buildOne:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuDlauJfnG05WAQs13Jx_Z8R5vR67nncPSM0dK1i4ohEL50UOFK1WTz-oAOero8E-i0Ytla49HH3u8SScnAy2Rs2T1FHTQRgkHmjdP1ziYnmX4loroHmr3-ln0nRK23OK-5OcKN_5wnjdgmgjF2C43PBe_2c1oMUeqFLjeBJJSKColJQfmPC2f1eDTAR55VTsedkWqb91pPpF_zr-bJhnBUk-M5q4HrJOCaV8tLr3x0Arl5ooT7ErtmgvaUxgugKM4P36WdDtHL74Vxv',
  buildTwo:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAKQAV6wn0RzGHaAUEqlwAdIwjnOXzLa40aEGHcid2IYbTDYz6TNYv9feumYIeafeNi0yk7Cr3BNCeJxKqlQ-gh_7rHhNAS-8EFMpWATHVZZBieVQBmxSInsZCc8ON5KPcLDEBoJiuulZcr6RebHoJ3Rv6OOKyatTqcOaD0FuSlrqxTujJ59Vrs4DxVmfwyJ_Kl13uIW_Ws3-gl39fh2_pNMU6eEZ6Z0g8oM1qeOEFhMNcqtIowNjG4LIYiddk_2FT3QSgNX15lIcJQ',
  tire: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBCn-snJF2k2D46yV3tgLhoq3K53ahoUE-R3ifsi2id--dgaPL7b9GQa76-ch9GkCDcHfI--vs5NKLfBOe7tFtHFUzY2cosPTBt7pHkOfpYvsP05UiLaWVaaRK9ooN3U60bjk-VzL7S7ksdvQuoErBWEAqj47DXvI7X_An9BLOqdSxPdR6m3Zv6CZYwczR1SbYr6vpgoz6Hu4Re5UL12gL0ZtenQgDQcTONAKRW2fR2_hq1ohTRVfNWV13BAUmWj6nQPSa9V1GUqdKO',
  sprocket:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuATk0EEPYMfkhK1hTo-bTsYOfqbnBrOq2xtLAv11uFxgJskPfWGO0LKb94wPS220H0EdhL8KjOAVQABcQoMIR6NRMUSIoi2lD_foNPfsXCI1e0kke0iRnJfDQy59S4t88F4l1kPYeVfCTOe2hxqlBikYFEjcuniJPivIXHp0klB2N2tW7L49lXnGLe3vZJPTyS1HseSCM6T8RDxQYWa1A9hzXPWqCStuSueBlS8xCSahPNLcQz50x4O5OHLYV82zC7pl5yP-0oczcqB',
  brakes:
    'https://lh3.googleusercontent.com/aida-public/AB6AXuDfgFwzixF0j5dUQ8vHCzvgPQxUprsNGd2nhv5klmjA0Zhfd6BxegbOpoKeKZM_IY4cpgdX4VPHRaFDg0Shoy8AoBkOwB1HCcMbj3UrmMsAjXRI8NrFtTmyt2dqkl01OY0oPyrWS70Mv3RA_q97lTv6LxkjG-ZcAmHWDTYvzXF9DfRJoyO7zl_tV5--_ZrC5D8Bq2bt1pLEZa8b_UDvaKDR72xHKxvUugmHxbaOrMs_JnHn1SGhR14SjRdUTtPjyhpM3CWKwTjHnryN',
}

const bookingSchema = z
  .object({
    service_name: z.string().min(2, 'Service is required'),
    customer_name: z.string().min(2, 'Customer name is required'),
    customer_email: z.email('Enter a valid email'),
    starts_at: z.string().min(1, 'Start time is required'),
    ends_at: z.string().min(1, 'End time is required'),
    notes: z.string().max(2000).optional(),
  })
  .refine((value) => new Date(value.ends_at) > new Date(value.starts_at), {
    message: 'End time must be after start time',
    path: ['ends_at'],
  })

const loginSchema = z.object({
  email: z.email('Enter a valid email'),
  password: z.string().min(1, 'Password is required'),
})

function toInputDateTime(date) {
  const offset = date.getTimezoneOffset() * 60_000
  return new Date(date.getTime() - offset).toISOString().slice(0, 16)
}

function defaultBookingTimes() {
  const start = new Date()
  start.setDate(start.getDate() + 1)
  start.setHours(10, 0, 0, 0)

  const end = new Date(start)
  end.setHours(start.getHours() + 1)

  return {
    starts_at: toInputDateTime(start),
    ends_at: toInputDateTime(end),
  }
}

async function fetchMe() {
  const response = await api.get('/api/auth/me')
  return response.data.data
}

async function fetchBookings() {
  const response = await api.get('/api/bookings')
  return response.data.data
}

function useMe() {
  return useQuery({
    queryKey: ['me'],
    queryFn: fetchMe,
    retry: false,
  })
}

function BrandHeader() {
  return (
    <header className="sticky top-0 z-50 border-b border-slate-200 bg-white">
      <div className="flex w-full items-center justify-between gap-4 px-4 py-4 sm:px-8 lg:px-12">
        <Link className="flex items-center gap-3" to="/">
          <span className="grid h-10 w-10 place-items-center bg-apex-primary text-white">
            <Bike className="h-5 w-5" />
          </span>
          <span className="brand-wordmark">APEX MOTORWORKS</span>
        </Link>
        <nav className="hidden items-center gap-7 text-sm font-bold uppercase md:flex">
          <NavLink className={topNavClass} to="/services">
            Service
          </NavLink>
          <NavLink className={topNavClass} to="/booking">
            Booking
          </NavLink>
          <NavLink className={topNavClass} to="/dashboard">
            Operations
          </NavLink>
        </nav>
        <Link className="apex-button px-5 py-3 text-xs sm:px-8" to="/booking">
          Book Service
        </Link>
      </div>
    </header>
  )
}

function topNavClass({ isActive }) {
  return isActive
    ? 'border-b-2 border-apex-red pb-1 text-apex-red'
    : 'text-slate-500 hover:text-apex-primary'
}

function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-white px-4 py-12 sm:px-8 lg:px-12">
      <div className="flex flex-col justify-between gap-8 md:flex-row md:items-center">
        <div>
          <p className="font-bold uppercase text-apex-primary">
            APEX MOTORWORKS
          </p>
          <p className="mt-2 text-xs font-bold uppercase text-slate-400">
            © 2026 APEX MOTORWORKS. Precision engineered.
          </p>
        </div>
        <div className="flex flex-wrap gap-6 text-xs font-bold uppercase text-slate-400">
          {['Privacy', 'Terms', 'Compliance', 'Contact'].map((item) => (
            <a
              className="hover:text-apex-primary hover:underline"
              href="#"
              key={item}
            >
              {item}
            </a>
          ))}
        </div>
      </div>
    </footer>
  )
}

function PublicLayout() {
  return (
    <div className="min-h-screen bg-apex-bg text-apex-ink">
      <BrandHeader />
      <Outlet />
      <Footer />
    </div>
  )
}

function LandingPage() {
  const features = [
    [
      '01.',
      'Advanced Tuning',
      'Dyno-tested maps for optimal power delivery and throttle response across all RPM ranges.',
    ],
    [
      '02.',
      'Bespoke Builds',
      'From frame geometry to engine internals, every build becomes a focused mechanical statement.',
    ],
    [
      '03.',
      'Technical Mastery',
      'Factory-trained technicians for high-performance European and Japanese platforms.',
    ],
  ]

  return (
    <main>
      <section className="relative isolate min-h-[calc(100vh-73px)] overflow-hidden bg-apex-panel px-4 py-16 sm:px-8 lg:px-12">
        <div className="grid min-h-[680px] items-center gap-10 lg:grid-cols-2">
          <div className="relative z-10 max-w-3xl">
            <p className="eyebrow mb-5 text-apex-red">
              MAD APE PERFORMANCE DIVISION
            </p>
            <h1 className="hero-title mb-8 max-w-4xl">
              Engineered for the discerning rider.
            </h1>
            <p className="max-w-xl text-lg leading-8 text-slate-600">
              Precision-tuned performance and bespoke motorcycle craftsmanship.
              We elevate mechanical integrity to an art form.
            </p>
            <div className="mt-10 flex flex-col gap-4 sm:flex-row">
              <Link className="apex-button px-8 py-4" to="/services">
                View Services
              </Link>
              <Link className="apex-button-outline px-8 py-4" to="/booking">
                Our Process
              </Link>
            </div>
          </div>
          <div className="h-[460px] overflow-hidden border border-slate-300 lg:absolute lg:right-0 lg:top-0 lg:h-full lg:w-1/2 lg:border-y-0 lg:border-r-0">
            <img
              alt="Custom high-performance cafe racer in a minimalist workshop"
              className="h-full w-full object-cover"
              src={images.hero}
            />
          </div>
        </div>
      </section>

      <section className="grid gap-8 bg-white px-4 py-16 sm:px-8 md:grid-cols-3 lg:px-12 lg:py-20">
        {features.map(([number, title, description]) => (
          <article className="border-l border-slate-200 py-2 pl-7" key={title}>
            <h3 className="text-3xl font-bold">{number}</h3>
            <p className="eyebrow mt-4 text-apex-red">{title}</p>
            <p className="mt-4 leading-7 text-slate-600">{description}</p>
          </article>
        ))}
      </section>

      <section className="bg-apex-panel px-4 py-16 sm:px-8 lg:px-12 lg:py-20">
        <div className="mb-10 flex items-end justify-between gap-6">
          <h2 className="section-title max-w-xl">Current workshop selection</h2>
          <div className="flex gap-3">
            <button
              className="icon-button"
              type="button"
              aria-label="Previous project"
            >
              <ArrowLeft className="h-5 w-5" />
            </button>
            <button
              className="icon-button"
              type="button"
              aria-label="Next project"
            >
              <ArrowRight className="h-5 w-5" />
            </button>
          </div>
        </div>
        <div className="grid gap-8 lg:grid-cols-[2fr_1fr]">
          <ProjectCard
            image={images.engine}
            label="Project Genesis"
            title="APE-X Custom Cafe"
          />
          <div className="grid gap-8">
            <ProjectCard
              compact
              image={images.helmet}
              label="Accessories"
              title="Carbon Performance Series"
            />
            <div className="flex min-h-72 flex-col justify-between bg-apex-red p-8 text-white">
              <h3 className="text-3xl font-bold uppercase">
                Ready for the track?
              </h3>
              <p className="leading-7">
                Schedule your performance consultation today.
              </p>
              <Link
                className="border border-white px-6 py-4 text-center text-xs font-bold uppercase hover:bg-white hover:text-apex-red"
                to="/booking"
              >
                Book now
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="grid items-center gap-12 bg-white px-4 py-16 sm:px-8 lg:grid-cols-[0.85fr_1.15fr] lg:px-12 lg:py-20">
        <img
          alt="Industrial motorcycle workshop"
          className="aspect-[4/5] w-full border border-apex-primary object-cover"
          src={images.workshop}
        />
        <div>
          <p className="eyebrow mb-6 text-apex-red">The workshop philosophy</p>
          <h2 className="hero-title mb-8 max-w-4xl">
            Clinical precision. Raw performance.
          </h2>
          <div className="grid gap-8">
            <FeatureRow
              icon={Factory}
              title="Geometric Integrity"
              text="Every modification respects the original architecture while enhancing rigidity."
            />
            <FeatureRow
              icon={BarChart3}
              title="Data-Driven Diagnostics"
              text="Telemetry-grade diagnostic workflows refine engine behavior before delivery."
            />
          </div>
        </div>
      </section>

      <CtaBand />
    </main>
  )
}

function ProjectCard({ compact = false, image, label, title }) {
  return (
    <article className="group border border-slate-200 bg-white">
      <div
        className={
          compact
            ? 'aspect-square overflow-hidden'
            : 'aspect-[16/9] overflow-hidden'
        }
      >
        <img
          alt={title}
          className="h-full w-full object-cover grayscale transition duration-700 group-hover:scale-105 group-hover:grayscale-0"
          src={image}
        />
      </div>
      <div className="flex items-center justify-between gap-4 p-6 sm:p-8">
        <div>
          <p className="eyebrow text-apex-red">{label}</p>
          <h3
            className={
              compact
                ? 'mt-2 text-lg font-bold uppercase'
                : 'mt-2 text-3xl font-bold uppercase'
            }
          >
            {title}
          </h3>
        </div>
        <ArrowRight className="h-6 w-6 -rotate-45" />
      </div>
    </article>
  )
}

function FeatureRow({ icon: Icon, title, text }) {
  return (
    <div className="flex gap-5">
      <span className="grid h-12 w-12 shrink-0 place-items-center border border-apex-primary">
        <Icon className="h-5 w-5" />
      </span>
      <div>
        <h3 className="font-bold uppercase">{title}</h3>
        <p className="mt-2 leading-7 text-slate-600">{text}</p>
      </div>
    </div>
  )
}

function CtaBand() {
  return (
    <section className="bg-apex-bg px-4 pb-16 sm:px-8 lg:px-12 lg:pb-20">
      <div className="border border-apex-primary bg-apex-panel p-8 text-center sm:p-12 lg:p-20">
        <h2 className="hero-title mb-8">Elevate your ride.</h2>
        <p className="mx-auto max-w-2xl text-lg leading-8 text-slate-600">
          Experience the difference of workshop excellence. Our team is ready to
          transform your performance expectations.
        </p>
        <div className="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
          <Link className="apex-button px-10 py-4" to="/booking">
            Get in touch
          </Link>
          <Link className="apex-button-outline px-10 py-4" to="/services">
            View catalog
          </Link>
        </div>
      </div>
    </section>
  )
}

function ServicesPage() {
  return (
    <main>
      <section className="relative isolate flex min-h-[620px] items-center overflow-hidden bg-apex-primary px-4 py-16 text-white sm:px-8 lg:px-12">
        <img
          alt="Motorcycle engine in a clinical workshop"
          className="absolute inset-0 -z-10 h-full w-full object-cover opacity-40"
          src={images.serviceHero}
        />
        <div className="max-w-4xl">
          <p className="eyebrow mb-4 text-white">Technical Excellence</p>
          <h1 className="hero-title mb-8 text-white">Precision engineering.</h1>
          <p className="max-w-xl text-lg leading-8 text-slate-300">
            High-performance motorcycle optimization, factory engine rebuilds,
            custom ECU remapping, and clinical workshop service.
          </p>
        </div>
      </section>
      <div className="flex flex-wrap gap-3 border-b border-slate-200 bg-apex-panel px-4 py-8 sm:px-8 lg:px-12">
        {[
          'All services',
          'Mechanical',
          'Electronics',
          'Suspension',
          'Maintenance',
        ].map((chip, index) => (
          <button
            className={index === 0 ? 'filter-chip-active' : 'filter-chip'}
            key={chip}
            type="button"
          >
            {chip}
          </button>
        ))}
      </div>
      <section className="grid gap-6 bg-white px-4 py-16 sm:px-8 lg:grid-cols-12 lg:px-12 lg:py-20">
        <WideServiceCard
          image={images.rebuild}
          number="PT. 01"
          title="Engine Rebuild"
          category="Mechanical"
          text="Complete blueprinted restoration using titanium valves and race-spec pistons."
        />
        <ServiceTile
          icon={Activity}
          title="ECU Remapping"
          category="Electronics"
          text="Custom fuel mapping and ignition timing optimization for intake and exhaust configurations."
        />
        <ServiceTile
          icon={Wrench}
          title="Suspension Tuning"
          category="Suspension"
          text="Shim-stack rebuilding and spring-rate calibration tailored to rider weight and track profile."
        />
        <WideServiceCard
          reverse
          image={images.chain}
          number="PT. 04"
          title="Drive System Service"
          category="Maintenance"
          text="Chain and sprocket replacement with precision alignment and tension calibration."
        />
      </section>
      <section className="bg-apex-panel px-4 py-16 sm:px-8 lg:px-12 lg:py-20">
        <h2 className="section-title mb-10">Workshop standards</h2>
        <div className="grid gap-6 md:grid-cols-3">
          <StandardCard
            icon={Cpu}
            title="Metrology Grade"
            text="Laser measuring tools accurate to 0.001mm for structural alignments."
          />
          <StandardCard
            icon={ShieldCheck}
            title="OEM Certified"
            text="Factory-trained service for European and Japanese performance marques."
          />
          <StandardCard
            icon={Gauge}
            title="Performance Validated"
            text="Each rebuild includes a diagnostic dyno run to verify delivery."
          />
        </div>
      </section>
    </main>
  )
}

function WideServiceCard({
  category,
  image,
  number,
  reverse = false,
  text,
  title,
}) {
  return (
    <article className="group border border-slate-200 lg:col-span-8">
      <div
        className={`grid h-full md:grid-cols-2 ${reverse ? 'md:[&>*:first-child]:order-2' : ''}`}
      >
        <div className="min-h-80 overflow-hidden">
          <img
            alt={title}
            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            src={image}
          />
        </div>
        <div className="flex flex-col justify-between p-8 lg:p-10">
          <div>
            <p className="eyebrow mb-3 text-apex-red">{category}</p>
            <h3 className="section-title mb-5">{title}</h3>
            <p className="leading-7 text-slate-600">{text}</p>
          </div>
          <div className="mt-8 flex items-center justify-between">
            <span className="text-3xl font-bold">{number}</span>
            <Link
              className="apex-button-outline px-6 py-3 text-xs"
              to="/booking"
            >
              Reserve
            </Link>
          </div>
        </div>
      </div>
    </article>
  )
}

function ServiceTile({ category, icon: Icon, text, title }) {
  return (
    <article className="flex min-h-96 flex-col border border-slate-200 bg-apex-panel p-8 lg:col-span-4">
      <Icon className="mb-12 h-10 w-10 text-apex-red" />
      <p className="eyebrow mb-3 text-apex-red">{category}</p>
      <h3 className="mb-5 text-3xl font-bold uppercase">{title}</h3>
      <p className="leading-7 text-slate-600">{text}</p>
      <div className="mt-auto flex items-center justify-between border-t border-slate-300 pt-6 text-xs font-bold uppercase">
        <span>Dyno validated</span>
        <ArrowRight className="h-5 w-5" />
      </div>
    </article>
  )
}

function StandardCard({ icon: Icon, text, title }) {
  return (
    <article className="border border-slate-200 bg-white p-8">
      <Icon className="mb-6 h-7 w-7 text-apex-red" />
      <h3 className="eyebrow mb-3 text-apex-primary">{title}</h3>
      <p className="leading-7 text-slate-600">{text}</p>
    </article>
  )
}

function BookingPage() {
  return (
    <main className="px-4 py-12 sm:px-8 lg:px-12 lg:py-20">
      <div className="mx-auto mb-14 flex max-w-6xl flex-col justify-between gap-8 md:flex-row md:items-end">
        <div>
          <p className="eyebrow mb-3 text-apex-red">Step 01 / 04</p>
          <h1 className="hero-title">Vehicle details</h1>
        </div>
        <div className="h-px w-full bg-slate-300 md:w-1/3">
          <div className="h-px w-1/4 bg-apex-red" />
        </div>
      </div>
      <div className="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[1.2fr_0.8fr]">
        <VehicleDetailsForm />
        <aside className="grid gap-6">
          <div className="relative aspect-[4/5] overflow-hidden border border-apex-primary">
            <img
              alt="Apex precision motorcycle service"
              className="h-full w-full object-cover"
              src={images.booking}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-apex-primary/70 to-transparent" />
            <div className="absolute bottom-6 left-6 right-6 text-white">
              <h3 className="text-3xl font-bold uppercase">Apex Precision</h3>
              <p className="mt-2 text-xs font-bold uppercase opacity-80">
                Certified technicians for performance marques.
              </p>
            </div>
          </div>
          <div className="border border-slate-200 bg-apex-panel p-6">
            <h4 className="eyebrow border-b border-slate-300 pb-3 text-apex-primary">
              Why choose Apex?
            </h4>
            <CheckLine text="OEM-grade diagnostic equipment for Ducati, Triumph, BMW, and more." />
            <CheckLine text="White-glove handling with dedicated lifts and climate-controlled bays." />
          </div>
        </aside>
      </div>
    </main>
  )
}

function VehicleDetailsForm() {
  return (
    <form className="grid gap-8">
      <div className="grid gap-6 md:grid-cols-2">
        <FieldShell label="Motorcycle Make">
          <select className="apex-input">
            <option>Ducati</option>
            <option>Triumph</option>
            <option>BMW</option>
            <option>KTM</option>
          </select>
        </FieldShell>
        <FieldShell label="Model Specification">
          <input className="apex-input" placeholder="Panigale V4S" />
        </FieldShell>
      </div>
      <div className="grid gap-6 md:grid-cols-2">
        <FieldShell label="Production Year">
          <input className="apex-input" placeholder="2026" type="number" />
        </FieldShell>
        <FieldShell label="Chassis / VIN">
          <input className="apex-input" placeholder="Enter last 6 digits" />
        </FieldShell>
      </div>
      <div>
        <p className="eyebrow mb-4 text-slate-500">Primary Service Intent</p>
        <div className="grid gap-3 sm:grid-cols-3">
          <IntentButton icon={Cog} text="Technical Inspection" />
          <IntentButton icon={Gauge} text="Dyno Tuning" />
          <IntentButton icon={CalendarDays} text="Scheduled Service" />
        </div>
      </div>
      <div className="flex flex-col justify-between gap-4 border-t border-slate-300 pt-8 sm:flex-row sm:items-center">
        <Link
          className="inline-flex items-center gap-2 text-xs font-bold uppercase text-slate-500 hover:text-apex-primary"
          to="/"
        >
          <ArrowLeft className="h-4 w-4" />
          Cancel
        </Link>
        <Link className="apex-button px-8 py-4" to="/dashboard">
          Confirm and continue
        </Link>
      </div>
    </form>
  )
}

function FieldShell({ children, label }) {
  return (
    <label className="grid gap-3">
      <span className="eyebrow text-slate-500">{label}</span>
      {children}
    </label>
  )
}

function IntentButton({ icon: Icon, text }) {
  return (
    <button
      className="group flex min-h-36 flex-col items-start justify-between border border-slate-300 bg-white p-6 text-left hover:border-apex-red"
      type="button"
    >
      <Icon className="h-6 w-6 text-slate-500 group-hover:text-apex-red" />
      <span className="text-xs font-bold uppercase">{text}</span>
    </button>
  )
}

function CheckLine({ text }) {
  return (
    <p className="mt-4 flex gap-3 text-sm leading-6 text-slate-600">
      <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-apex-red" />
      {text}
    </p>
  )
}

function ProtectedAdmin() {
  const me = useMe()

  if (me.isLoading) {
    return <StatusScreen icon={RefreshCw} title="Loading workshop" />
  }

  if (!me.data) {
    return <LoginPanel />
  }

  return <AdminLayout user={me.data} />
}

function LoginPanel() {
  const queryClient = useQueryClient()
  const {
    formState: { errors },
    handleSubmit,
    register,
    setError,
  } = useForm({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: 'admin@example.com',
      password: 'password',
    },
  })

  const login = useMutation({
    mutationFn: async (payload) => {
      await csrf()
      const response = await api.post('/api/auth/login', payload)
      return response.data.data
    },
    onSuccess: (user) => {
      queryClient.setQueryData(['me'], user)
      queryClient.invalidateQueries({ queryKey: ['bookings'] })
    },
    onError: () => {
      setError('email', { message: 'Unable to sign in with those details' })
    },
  })

  return (
    <main className="grid min-h-screen place-items-center bg-apex-bg px-4 py-12">
      <form
        className="w-full max-w-md border border-apex-primary bg-white p-8"
        onSubmit={handleSubmit((values) => login.mutate(values))}
      >
        <Link
          className="mb-8 inline-flex items-center gap-2 text-xs font-bold uppercase text-slate-500"
          to="/"
        >
          <ArrowLeft className="h-4 w-4" />
          Back to site
        </Link>
        <div className="mb-8">
          <span className="grid h-12 w-12 place-items-center bg-apex-primary text-white">
            <LogIn className="h-5 w-5" />
          </span>
          <p className="eyebrow mt-5 text-apex-red">Technical Operations</p>
          <h1 className="section-title mt-2">Service panel sign in</h1>
        </div>
        <FormField
          error={errors.email?.message}
          label="Email"
          registration={register('email')}
          type="email"
        />
        <FormField
          error={errors.password?.message}
          label="Password"
          registration={register('password')}
          type="password"
        />
        <button
          className="apex-button mt-4 w-full py-4"
          disabled={login.isPending}
        >
          {login.isPending ? 'Signing in' : 'Sign in'}
        </button>
      </form>
    </main>
  )
}

function AdminLayout({ user }) {
  const queryClient = useQueryClient()
  const logout = useMutation({
    mutationFn: async () => {
      await csrf()
      await api.post('/api/auth/logout')
    },
    onSettled: () => {
      queryClient.removeQueries()
      queryClient.setQueryData(['me'], null)
    },
  })

  return (
    <div className="min-h-screen bg-apex-bg text-apex-ink lg:flex">
      <aside className="border-r border-slate-200 bg-slate-50 lg:fixed lg:inset-y-0 lg:left-0 lg:w-64">
        <div className="px-6 py-8">
          <p className="text-xl font-black uppercase text-blue-900">
            Service Panel
          </p>
          <p className="mt-1 text-xs font-bold uppercase text-slate-400">
            Technical operations
          </p>
        </div>
        <nav className="grid gap-1 px-4">
          <AdminNavLink
            icon={LayoutDashboard}
            label="Dashboard"
            to="/dashboard"
            end
          />
          <AdminNavLink
            icon={Hammer}
            label="Work Orders"
            to="/dashboard/work-orders"
          />
          <AdminNavLink
            icon={PackageCheck}
            label="Inventory"
            to="/dashboard/inventory"
          />
          <AdminNavLink
            icon={CircleGauge}
            label="Customer View"
            to="/dashboard/customer"
          />
        </nav>
        <div className="mt-6 border-t border-slate-200 p-4 lg:absolute lg:bottom-0 lg:left-0 lg:right-0">
          <Link
            className="apex-button mb-4 w-full py-4 text-center"
            to="/dashboard/work-orders"
          >
            New order
          </Link>
          <button
            className="flex w-full items-center gap-3 px-4 py-3 text-sm font-bold uppercase text-slate-500 hover:bg-slate-200"
            disabled={logout.isPending}
            onClick={() => logout.mutate()}
            type="button"
          >
            <LogOut className="h-5 w-5" />
            {logout.isPending ? 'Signing out' : 'Logout'}
          </button>
        </div>
      </aside>
      <div className="flex min-h-screen flex-1 flex-col lg:ml-64">
        <header className="sticky top-0 z-40 border-b border-slate-200 bg-white px-4 py-5 sm:px-8 lg:px-12">
          <div className="flex items-center justify-between gap-4">
            <div>
              <p className="eyebrow text-slate-500">Operational Overview</p>
              <h1 className="text-2xl font-bold uppercase text-apex-primary">
                Workshop Central
              </h1>
            </div>
            <div className="flex items-center gap-4">
              <Search className="h-5 w-5 text-slate-500" />
              <div className="hidden text-right sm:block">
                <p className="text-sm font-bold">{user.name}</p>
                <p className="text-xs text-slate-500">{user.email}</p>
              </div>
              <span className="grid h-10 w-10 place-items-center bg-slate-200 font-bold text-apex-primary">
                {user.name?.slice(0, 1) ?? 'A'}
              </span>
            </div>
          </div>
        </header>
        <Outlet />
        <Footer />
      </div>
    </div>
  )
}

function AdminNavLink({ end = false, icon: Icon, label, to }) {
  return (
    <NavLink
      className={({ isActive }) =>
        `flex items-center gap-3 px-4 py-3 text-sm font-bold uppercase ${
          isActive
            ? 'border-l-4 border-apex-red bg-blue-900 text-white'
            : 'text-slate-600 hover:bg-slate-200'
        }`
      }
      end={end}
      to={to}
    >
      <Icon className="h-5 w-5" />
      {label}
    </NavLink>
  )
}

function AdminOverview() {
  const bookings = useQuery({
    queryKey: ['bookings'],
    queryFn: fetchBookings,
  })

  const events = useMemo(
    () =>
      (bookings.data ?? []).map((booking) => ({
        id: String(booking.id),
        title: `${booking.service_name} - ${booking.customer_name}`,
        start: booking.starts_at,
        end: booking.ends_at,
        extendedProps: booking,
      })),
    [bookings.data],
  )

  return (
    <main className="grid gap-10 p-4 sm:p-8 lg:p-12">
      <section className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <MetricCard
          label="Daily bookings"
          value={bookings.data?.length ?? 0}
          note="+12% vs YTD"
        />
        <MetricCard label="Active builds" value="08" note="3 assigned" />
        <MetricCard label="Parts inventory" value="94%" note="8 critical low" />
        <MetricCard label="Efficiency rate" value="88" note="Trending up" />
      </section>

      <section className="grid gap-8 xl:grid-cols-[360px_1fr]">
        <aside className="grid gap-6">
          <UserPanel />
          <BookingForm />
        </aside>
        <div className="border border-slate-200 bg-white p-4 sm:p-6">
          <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
              <p className="eyebrow text-apex-red">Live Calendar</p>
              <h2 className="section-title">Booking calendar</h2>
            </div>
            <button
              className="apex-button-outline px-4 py-3"
              disabled={bookings.isFetching}
              onClick={() => bookings.refetch()}
              type="button"
            >
              <RefreshCw className="h-4 w-4" />
              Refresh
            </button>
          </div>
          {bookings.isError ? (
            <StatusScreen icon={AlertCircle} title="Could not load bookings" />
          ) : (
            <FullCalendar
              allDaySlot={false}
              events={events}
              eventTimeFormat={{
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
              }}
              headerToolbar={{
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay',
              }}
              height="auto"
              initialView="timeGridWeek"
              plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
              slotMinTime="07:00:00"
              slotMaxTime="20:00:00"
            />
          )}
        </div>
      </section>
    </main>
  )
}

function MetricCard({ label, note, value }) {
  return (
    <article className="flex h-44 flex-col justify-between border border-apex-primary bg-white p-6">
      <p className="eyebrow text-slate-500">{label}</p>
      <div className="flex items-end justify-between gap-4">
        <span className="text-5xl font-bold leading-none">{value}</span>
        <span className="text-xs font-bold uppercase text-apex-red">
          {note}
        </span>
      </div>
    </article>
  )
}

function UserPanel() {
  const { data: user } = useMe()

  return (
    <article className="border border-slate-200 bg-white p-6">
      <p className="eyebrow text-slate-500">Signed in as</p>
      <h2 className="mt-2 text-xl font-bold">{user?.name}</h2>
      <p className="text-sm text-slate-500">{user?.email}</p>
      <div className="mt-4 flex flex-wrap gap-2">
        {(user?.roles ?? []).map((role) => (
          <span className="badge" key={role}>
            {role}
          </span>
        ))}
      </div>
    </article>
  )
}

function BookingForm() {
  const queryClient = useQueryClient()
  const defaults = defaultBookingTimes()
  const {
    formState: { errors },
    handleSubmit,
    register,
    reset,
  } = useForm({
    resolver: zodResolver(bookingSchema),
    defaultValues: {
      service_name: 'Performance consultation',
      customer_name: '',
      customer_email: '',
      starts_at: defaults.starts_at,
      ends_at: defaults.ends_at,
      notes: '',
    },
  })

  const createBooking = useMutation({
    mutationFn: async (payload) => {
      await csrf()
      const response = await api.post('/api/bookings', payload)
      return response.data.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] })
      reset({
        service_name: 'Performance consultation',
        customer_name: '',
        customer_email: '',
        ...defaultBookingTimes(),
        notes: '',
      })
    },
  })

  return (
    <form
      className="border border-slate-200 bg-white p-6"
      onSubmit={handleSubmit((values) => createBooking.mutate(values))}
    >
      <div className="mb-6">
        <CalendarPlus className="mb-4 h-8 w-8 text-apex-red" />
        <p className="eyebrow text-apex-red">New order</p>
        <h2 className="section-title">Create booking</h2>
      </div>
      <FormField
        error={errors.service_name?.message}
        label="Service"
        registration={register('service_name')}
      />
      <FormField
        error={errors.customer_name?.message}
        label="Customer"
        registration={register('customer_name')}
      />
      <FormField
        error={errors.customer_email?.message}
        label="Email"
        registration={register('customer_email')}
        type="email"
      />
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          error={errors.starts_at?.message}
          label="Starts"
          registration={register('starts_at')}
          type="datetime-local"
        />
        <FormField
          error={errors.ends_at?.message}
          label="Ends"
          registration={register('ends_at')}
          type="datetime-local"
        />
      </div>
      <label className="mb-5 block">
        <span className="mb-2 block text-xs font-bold uppercase text-slate-500">
          Notes
        </span>
        <textarea className="input min-h-24 resize-y" {...register('notes')} />
      </label>
      {createBooking.isSuccess ? (
        <Notice icon={CheckCircle2} text="Booking created" />
      ) : null}
      {createBooking.isError ? (
        <Notice danger icon={AlertCircle} text="Booking could not be saved" />
      ) : null}
      <button
        className="apex-button w-full py-4"
        disabled={createBooking.isPending}
      >
        {createBooking.isPending ? 'Creating' : 'Create booking'}
      </button>
    </form>
  )
}

function FormField({ error, label, registration, type = 'text' }) {
  return (
    <label className="mb-4 block">
      <span className="mb-2 block text-xs font-bold uppercase text-slate-500">
        {label}
      </span>
      <input className="input" type={type} {...registration} />
      <FieldError message={error} />
    </label>
  )
}

function FieldError({ message }) {
  if (!message) {
    return null
  }

  return <p className="mt-2 text-sm text-apex-red">{message}</p>
}

function Notice({ danger = false, icon: Icon, text }) {
  return (
    <div
      className={`mb-4 flex items-center gap-2 px-3 py-2 text-sm ${danger ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'}`}
    >
      <Icon className="h-4 w-4" />
      {text}
    </div>
  )
}

function WorkOrdersPage() {
  return (
    <main className="p-4 sm:p-8 lg:p-12">
      <div className="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
          <p className="eyebrow mb-2 text-apex-red">Active Workshop</p>
          <h2 className="section-title">Service Queue</h2>
        </div>
        <div className="border border-apex-primary px-5 py-3 text-xs font-bold uppercase">
          3 Technicians Active
        </div>
      </div>
      <div className="grid gap-6 lg:grid-cols-12">
        <PriorityJob />
        <SmallJob />
        <VelocityPanel />
        <ChecklistJob />
      </div>
    </main>
  )
}

function PriorityJob() {
  return (
    <article className="group border border-apex-primary bg-white p-6 lg:col-span-8">
      <div className="mb-8 flex justify-between gap-4">
        <div>
          <span className="mb-4 inline-block bg-apex-primary px-3 py-1 text-xs font-bold uppercase text-white">
            High Priority
          </span>
          <h3 className="section-title">Triumph Bobber</h3>
          <p className="text-xs font-bold uppercase text-slate-500">
            VIN: TR99201-B | 2022 Model
          </p>
        </div>
        <div className="text-right">
          <span className="block text-4xl font-bold text-slate-200">01</span>
          <span className="eyebrow text-apex-red">Stall 04</span>
        </div>
      </div>
      <div className="grid gap-6 border-t border-slate-100 pt-6 md:grid-cols-3">
        <Spec label="Primary Task" value="Valve Clearance Check" />
        <Spec label="Estimated Time" value="4.5 Hours" />
        <Spec label="Parts Status" value="Ready" />
      </div>
      <div className="mt-8 h-56 overflow-hidden bg-slate-900">
        <img
          alt="Triumph Bobber service"
          className="h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105"
          src={images.buildOne}
        />
      </div>
    </article>
  )
}

function SmallJob() {
  return (
    <article className="flex flex-col justify-between border border-slate-200 bg-slate-50 p-6 lg:col-span-4">
      <div>
        <div className="mb-6 flex justify-between">
          <p className="eyebrow text-slate-500">Maintenance</p>
          <CalendarDays className="h-5 w-5 text-slate-400" />
        </div>
        <h3 className="section-title">Honda CB500X</h3>
        <p className="eyebrow mt-2 text-slate-500">Service #8821</p>
        <div className="mt-8 divide-y divide-slate-200">
          <SpecRow label="Task" value="Tire Swap" />
          <SpecRow label="Front" value="110/80 R19" />
          <SpecRow label="Rear" value="160/60 R17" />
        </div>
      </div>
      <button className="apex-button-outline mt-8 w-full py-3" type="button">
        Start clock
      </button>
    </article>
  )
}

function VelocityPanel() {
  return (
    <article className="grid place-items-center border border-slate-200 bg-white p-8 text-center lg:col-span-4">
      <CircleGauge className="h-12 w-12 text-blue-900" />
      <h3 className="mt-4 text-3xl font-bold uppercase">Workshop Velocity</h3>
      <p className="mt-3 leading-7 text-slate-600">
        Efficiency is up 12% this week. Lead time average: 3.2 days.
      </p>
      <div className="mt-6 h-1 w-full bg-slate-100">
        <div className="h-1 w-3/4 bg-apex-red" />
      </div>
    </article>
  )
}

function ChecklistJob() {
  return (
    <article className="grid gap-8 border border-apex-primary bg-white p-6 lg:col-span-8 lg:grid-cols-2">
      <div className="flex flex-col justify-between">
        <div>
          <h3 className="section-title">Ducati Multistrada</h3>
          <p className="eyebrow mt-2 text-apex-red">In Progress</p>
          <div className="mt-8 grid gap-4">
            <Task done text="Oil and Filter Change" />
            <Task active text="Chain Tensioning and Lube" />
            <Task text="ECU Flash Update" />
          </div>
        </div>
        <div className="mt-8 flex items-center gap-4 border-t border-slate-200 pt-6">
          <div className="h-10 w-10 bg-slate-200" />
          <div>
            <p className="eyebrow">Technician</p>
            <p className="font-bold">M. Rossi</p>
          </div>
        </div>
      </div>
      <img
        alt="Chain maintenance"
        className="h-full min-h-80 w-full object-cover grayscale"
        src={images.chain}
      />
    </article>
  )
}

function Spec({ label, value }) {
  return (
    <div>
      <p className="eyebrow mb-2 text-slate-400">{label}</p>
      <p className="text-lg font-bold">{value}</p>
    </div>
  )
}

function SpecRow({ label, value }) {
  return (
    <div className="flex justify-between gap-4 py-4">
      <span className="eyebrow text-slate-500">{label}</span>
      <span className="font-bold">{value}</span>
    </div>
  )
}

function Task({ active = false, done = false, text }) {
  return (
    <div className="flex items-center gap-3">
      {done ? (
        <CheckCircle2 className="h-5 w-5 text-slate-400" />
      ) : (
        <span
          className={`h-5 w-5 border ${active ? 'border-apex-primary' : 'border-slate-300'}`}
        />
      )}
      <span
        className={
          done
            ? 'text-slate-400 line-through'
            : active
              ? 'font-bold'
              : 'text-slate-400'
        }
      >
        {text}
      </span>
    </div>
  )
}

function InventoryPage() {
  const categories = [
    ['Tires', '142 units', images.tire, false],
    ['Sprockets', '08 units', images.sprocket, true],
    ['Brake Pads', '216 units', images.brakes, false],
    ['Drive Chains', '32 units', images.chain, false],
  ]

  return (
    <main className="p-4 sm:p-8 lg:p-12">
      <div className="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
          <p className="eyebrow mb-2 text-apex-red">Stock Monitoring</p>
          <h1 className="hero-title">Parts Inventory</h1>
        </div>
        <div className="flex gap-4">
          <MiniStat label="Critical Alerts" value="04" />
          <MiniStat label="Total SKU" value="1,248" />
        </div>
      </div>
      <section className="mb-12 grid gap-6 lg:grid-cols-12">
        <div className="relative overflow-hidden border-2 border-apex-red bg-white p-8 lg:col-span-8">
          <div className="relative z-10">
            <p className="eyebrow mb-4 text-apex-red">
              Immediate Attention Required
            </p>
            <h2 className="section-title mb-8 max-w-xl">
              Global supply delay: O-Ring Chains
            </h2>
            <div className="grid gap-6 sm:grid-cols-3">
              <Spec label="In Stock" value="02 Units" />
              <Spec label="On Order" value="48 Units" />
              <Spec label="ETA" value="14 Days" />
            </div>
            <button className="apex-button mt-10 px-8 py-4" type="button">
              Re-route shipment
            </button>
          </div>
          <img
            alt="Motorcycle chain"
            className="absolute bottom-0 right-0 h-full w-1/2 object-cover opacity-10 grayscale"
            src={images.chain}
          />
        </div>
        <div className="grid gap-6 lg:col-span-4">
          <div className="bg-apex-primary p-8 text-white">
            <p className="eyebrow text-slate-400">Inventory Health</p>
            <p className="mt-8 text-7xl font-bold">94%</p>
          </div>
          <div className="border border-slate-200 bg-white p-8">
            <p className="eyebrow text-slate-400">Active Orders</p>
            <p className="mt-8 text-3xl font-bold uppercase">12 Active</p>
            <div className="mt-6 h-1 bg-slate-100">
              <div className="h-1 w-2/3 bg-blue-900" />
            </div>
          </div>
        </div>
      </section>
      <section>
        <div className="mb-8 flex items-center justify-between">
          <h2 className="section-title">Core Components</h2>
          <div className="flex gap-2">
            <button className="icon-button" type="button" aria-label="Previous">
              <ChevronLeft className="h-5 w-5" />
            </button>
            <button className="icon-button" type="button" aria-label="Next">
              <ChevronRight className="h-5 w-5" />
            </button>
          </div>
        </div>
        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
          {categories.map(([title, stock, image, low]) => (
            <InventoryCard
              image={image}
              key={title}
              low={low}
              stock={stock}
              title={title}
            />
          ))}
        </div>
      </section>
      <LedgerTable />
    </main>
  )
}

function MiniStat({ label, value }) {
  return (
    <div className="border border-slate-200 bg-white px-5 py-4 text-right">
      <p className="eyebrow text-slate-400">{label}</p>
      <p className="text-3xl font-bold text-apex-primary">{value}</p>
    </div>
  )
}

function InventoryCard({ image, low, stock, title }) {
  return (
    <article
      className={`group border bg-white ${low ? 'border-apex-red' : 'border-slate-200 hover:border-apex-primary'}`}
    >
      <div className="aspect-square overflow-hidden">
        <img
          alt={title}
          className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
          src={image}
        />
      </div>
      <div className="p-6">
        <h3 className={`font-bold uppercase ${low ? 'text-apex-red' : ''}`}>
          {title}
        </h3>
        <p className="mt-1 text-xs font-bold uppercase text-slate-400">
          Apex approved suppliers
        </p>
        <div className="mt-5 flex justify-between text-xs font-bold uppercase">
          <span className={low ? 'text-apex-red' : 'text-apex-primary'}>
            {stock}
          </span>
          <span>{low ? 'Low stock' : 'Optimal'}</span>
        </div>
      </div>
    </article>
  )
}

function LedgerTable() {
  const rows = [
    ['BR-902', 'Brembo Z04 Sintered Pads', 'Braking', '42', 'Optimal'],
    ['CH-520G', 'DID 520 ZVM-X Gold Chain', 'Transmission', '02', 'Critical'],
    ['FL-HP4', 'K&N KN-204 Oil Filter', 'Service', '18', 'Optimal'],
  ]

  return (
    <section className="mt-14 bg-slate-50 py-10">
      <div className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <h2 className="section-title">Real-Time Ledger</h2>
        <div className="flex items-center gap-2 border-b border-slate-300 px-1 py-2">
          <Search className="h-4 w-4 text-slate-400" />
          <input
            className="bg-transparent text-xs font-bold uppercase outline-none"
            placeholder="Search by part no."
          />
        </div>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full min-w-[760px] border-separate border-spacing-y-3 text-left text-sm">
          <thead className="text-xs font-bold uppercase text-slate-400">
            <tr>
              <th className="px-5">Part ID</th>
              <th className="px-5">Description</th>
              <th className="px-5">Category</th>
              <th className="px-5">Stock</th>
              <th className="px-5 text-right">Status</th>
            </tr>
          </thead>
          <tbody>
            {rows.map(([id, description, category, stock, status]) => (
              <tr
                className={`bg-white ${status === 'Critical' ? 'outline outline-2 outline-apex-red' : ''}`}
                key={id}
              >
                <td className="px-5 py-5 font-bold">{id}</td>
                <td
                  className={`px-5 py-5 font-bold uppercase ${status === 'Critical' ? 'text-apex-red' : ''}`}
                >
                  {description}
                </td>
                <td className="px-5 py-5">{category}</td>
                <td className="px-5 py-5 font-bold">{stock}</td>
                <td
                  className={`px-5 py-5 text-right font-bold uppercase ${status === 'Critical' ? 'text-apex-red' : 'text-blue-700'}`}
                >
                  {status}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  )
}

function CustomerDashboardPage() {
  return (
    <main className="p-4 sm:p-8 lg:p-12">
      <div className="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
          <p className="eyebrow mb-2 text-apex-red">Precision Tracking</p>
          <h2 className="section-title">Customer Dashboard</h2>
        </div>
        <p className="text-sm font-bold uppercase text-slate-500">
          Last sync: 14:22
        </p>
      </div>
      <section className="grid gap-6 lg:grid-cols-12">
        <article className="border border-apex-primary bg-white p-8 lg:col-span-8">
          <div className="flex justify-between gap-4">
            <div>
              <h3 className="section-title">Ducati Panigale V4</h3>
              <div className="mt-3 flex flex-wrap gap-3">
                <span className="badge">VIN: ZDM123...890</span>
                <span className="badge badge-red">Active Service</span>
              </div>
            </div>
            <Settings className="h-9 w-9 text-apex-red" />
          </div>
          <div className="my-10 h-72 overflow-hidden">
            <img
              alt="Ducati Panigale service"
              className="h-full w-full object-cover grayscale"
              src={images.serviceHero}
            />
          </div>
          <div className="grid gap-6 md:grid-cols-4">
            <Spec label="Engine Health" value="98%" />
            <Spec label="Tire Pressure" value="34 / 36 PSI" />
            <Spec label="Desmo Service" value="4,200 KM" />
            <Spec label="Status" value="Diagnostics" />
          </div>
        </article>
        <article className="flex flex-col justify-between bg-apex-primary p-8 text-white lg:col-span-4">
          <div>
            <p className="eyebrow text-slate-400">Next Scheduled Appointment</p>
            <h3 className="mt-5 text-3xl font-bold uppercase">
              Annual Desmo Precision Check
            </h3>
            <div className="mt-8 grid gap-4">
              <InfoLine
                icon={CalendarDays}
                label="Date"
                value="October 24, 2026"
              />
              <InfoLine icon={ClockIcon} label="Time" value="09:00 AM" />
              <InfoLine
                icon={Factory}
                label="Location"
                value="Apex Milano Atelier"
              />
            </div>
          </div>
          <button
            className="mt-8 border border-white px-6 py-4 text-xs font-bold uppercase hover:bg-white hover:text-apex-primary"
            type="button"
          >
            Reschedule
          </button>
        </article>
      </section>
    </main>
  )
}

function ClockIcon(props) {
  return <CalendarDays {...props} />
}

function InfoLine({ icon: Icon, label, value }) {
  return (
    <div className="flex items-center gap-4">
      <Icon className="h-5 w-5 text-apex-red" />
      <div>
        <p className="text-xs font-bold uppercase text-slate-400">{label}</p>
        <p className="text-sm font-bold uppercase">{value}</p>
      </div>
    </div>
  )
}

function StatusScreen({ icon: Icon, title }) {
  return (
    <main className="grid min-h-screen place-items-center bg-apex-bg p-8">
      <div className="flex items-center gap-3 border border-slate-200 bg-white p-8 font-bold uppercase text-slate-600">
        <Icon className="h-5 w-5" />
        {title}
      </div>
    </main>
  )
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<PublicLayout />} path="/">
          <Route index element={<LandingPage />} />
          <Route element={<ServicesPage />} path="services" />
          <Route element={<BookingPage />} path="booking" />
        </Route>
        <Route element={<ProtectedAdmin />} path="/dashboard">
          <Route index element={<AdminOverview />} />
          <Route element={<WorkOrdersPage />} path="work-orders" />
          <Route element={<InventoryPage />} path="inventory" />
          <Route element={<CustomerDashboardPage />} path="customer" />
        </Route>
        <Route element={<Navigate replace to="/" />} path="*" />
      </Routes>
    </BrowserRouter>
  )
}
