/* ===================================================================
   Al Waab — Bilingual (AR/EN) i18n + AI Product Assistant
   Knowledge base sourced from the Al Waab Plastics CPVC catalogue.
=================================================================== */
(function () {
  "use strict";

  /* Inject Arabic font & Floating Button Styles */
  var f = document.createElement("link");
  f.rel = "stylesheet";
  f.href = "https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap";
  document.head.appendChild(f);

  var s = document.createElement("style");
  s.textContent = ".lang-btn { position: fixed; bottom: 30px; left: 30px; z-index: 2000; display: flex; align-items: center; gap: 8px; padding: 12px 22px; background: #0045ad; color: #fff; border: none; border-radius: 50px; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.2); font-family: 'Tajawal', sans-serif; font-weight: 700; font-size: 15px; transition: all 0.3s ease; width: auto !important; height: auto !important; } " +
    ".lang-btn:hover { transform: translateY(-5px); background: #003585; box-shadow: 0 12px 30px rgba(0,0,0,0.3); } " +
    ".quote-badge { position: fixed; bottom: 95px; left: 30px; z-index: 2000; background: #ff9800; color: #fff; padding: 10px 15px; border-radius: 12px; font-size: 13px; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: none; align-items: center; gap: 8px; cursor: pointer; border: none; font-family: 'Tajawal', sans-serif; width: max-content !important; height: auto !important; } " +
    ".product-card.reveal.in { opacity: 1; transform: translateY(0); } " +
    ".lang-btn svg { width: 20px; height: 20px; } " +
    "/* RTL Layout Fixes */ " +
    "[dir='rtl'] .lang-btn { left: auto; right: 30px; } " +
    "[dir='rtl'] .quote-badge { left: auto; right: 30px; } " +
    "[dir='rtl'] .logo-wrapper { margin-left: auto; margin-right: 0; } " +
    "[dir='rtl'] .nav-list { margin-right: auto; margin-left: 0; direction: rtl; } " +
    "/* Chatbot Link Fixes */ " +
    ".chatbot:not(.open) { pointer-events: none !important; } " +
    ".chatbot.open { pointer-events: auto !important; } " +
    ".cb-msg a { color: #0045ad !important; text-decoration: underline !important; pointer-events: auto !important; cursor: pointer !important; font-weight: 700; } " +
    ".cb-body { pointer-events: auto !important; } " +
    "@media (max-width: 768px) { .lang-btn { bottom: 20px; left: 20px; padding: 10px 18px; font-size: 14px; } " +
    "[dir='rtl'] .lang-btn { left: auto; right: 20px; } }";
  document.head.appendChild(s);

  /* ---------------- i18n DICTIONARY (English → Arabic) ---------------- */
  var DICT = {
    // Top bar / brand / nav
    "AL WAAB": "الوعب",
    "FlowGuard®": "FlowGuard®",
    "Plumbing Systems": "أنظمة السباكة",
    "Sun–Thu · 8:00 – 18:00": "الأحد – الخميس · 8:00 صباحاً – 6:00 مساءً",
    "Building Materials": "مواد البناء",
    "Building Materials · UAE": "مواد البناء",
    "Home": "الرئيسية",
    "About Us": "من نحن",
    "Products": "منتجاتنا",
    "Projects": "المشاريع والمنجزات",
    "Certificates": "الاعتمادات والشهادات", 
    "Distributors": "شبكة الموزعين",
    "Contact": "اتصل بنا", 
    "Request a Quote": "طلب عرض سعر",
    "Certificates & Approvals": "الشهادات والاعتمادات الفنية المعتمدة",
    "Distributors & Network": "الموزعون وشبكة التوزيع",

    // Hero
    "Official FlowGuard® CPVC Supplier — United Arab Emirates": "المورّد الرسمي لأنظمة FlowGuard® CPVC — الإمارات العربية المتحدة",
    "Certified Water Piping.": "أنابيب مياه معتمدة.",
    "Delivered with Confidence.": "نُسلّمها بثقة.",
    "We don't just supply pipes — we deliver safety, trust and certified performance. Al Waab Building Materials brings FlowGuard® CPVC hot & cold water systems to projects across the UAE.":
      "نحن لا نكتفي بتوريد الأنابيب، بل نقدم منظومة متكاملة من الأمان والجودة المعتمدة. توفر مؤسسة الوعب لمواد البناء أنظمة FlowGuard® CPVC للمياه الساخنة والباردة لمختلف المشاريع الإنشائية في دولة الإمارات.",
    "Explore Products": "تصفّح المنتجات",
    "Manufacturer Warranty": "ضمان المصنّع",
    "Certified Systems": "أنظمة معتمدة",
    "Day UAE Delivery": "أيام للتوصيل بالإمارات",

    // Trust
    "Approved & Certified by": "الاعتمادات والشهادات الدولية والمحلية",

    // About preview
    "Who We Are": "من نحن",
    "A Certified Partner — Not Just a Vendor": "شريككم التقني المعتمد — لسنا مجرد مورّدين",
    "Al Waab Building Materials Trading EST is the UAE arm of the Al Waab group, specialising in certified water piping systems. We supply genuine FlowGuard® CPVC pipes and fittings backed by full documentation, technical support and BOQ alignment.":
      "مؤسسة الوعب لتجارة مواد البناء هي الذراع الإماراتية لمجموعة الوعب، متخصصة في أنظمة أنابيب المياه المعتمدة. نورّد أنابيب وتوصيلات FlowGuard® CPVC الأصلية مدعومة بالوثائق الكاملة والدعم الفني ومطابقة جداول الكميات (BOQ).",
    "Our mission is simple: help consultants and contractors close compliance gaps and avoid project delays with piping that is safe, traceable and approval-ready.":
      "مهمتنا الأساسية: دعم الاستشاريين والمقاولين في تحقيق أعلى معايير المطابقة وتجنّب تأخير المشاريع من خلال توفير أنظمة أنابيب آمنة، قابلة للتتبّع، وجاهزة للاعتماد الفوري.",
    "WRAS & NSF certified": "معتمد WRAS و NSF",
    "Dubai Municipality approved": "معتمد من بلدية دبي",
    "Project-ready kits": "حزم جاهزة للمشاريع",
    "Full technical support": "دعم فني كامل",
    "More About Us": "المزيد عنّا",
    "10+": "+10", "Years of Trust": "أعوام من الثقة",

    // Products section
    "Our Products": "حلولنا ومنتجاتنا",
    "FlowGuard® CPVC Piping Systems": "أنظمة أنابيب FlowGuard® CPVC",
    "A complete, certified hot & cold water solution — engineered pipes and a full range of fittings for residential, commercial and industrial projects.":
      "حلول متكاملة ومعتمدة لأنظمة المياه الساخنة والباردة — أنابيب مصممة هندسياً مع مجموعة كاملة من التوصيلات للمشاريع السكنية والتجارية والصناعية.",
    "CPVC Pipes": "أنابيب CPVC",
    "High-performance chlorinated PVC pipes for hot & cold potable water — non-corrosive, fire-retardant and UV-stable.":
      "أنابيب CPVC فائقة الأداء للمياه الصالحة للشرب (الساخنة والباردة) — مقاومة للتآكل، مثبطة للهب، ومستقرة تماماً ضد الأشعة فوق البنفسجية.",
    "CPVC Fittings": "توصيلات CPVC",
    "A full range of elbows, tees, couplings, valves and adapters — precision-moulded for leak-proof, durable connections.":
      "تشكيلة واسعة من الأكواع، الوصلات، المحابس، والمهايئات — مصنعة بدقة لضمان وصلات متينة ومحكمة تمنع التسريب تماماً.",
    "Project-Ready Kits": "حزم جاهزة للمشاريع",
    "Pipes, fittings, joints and solvent cement supplied together — aligned to your BOQ with complete submittals.":
      "أنابيب وتوصيلات ووصلات ولاصق كيميائي تُورّد معاً — مطابقة لجدول الكميات مع كامل المستندات.",
    "View Details": "عرض التفاصيل",
    "Solvent Cement & Joints": "اللاصق الكيميائي والوصلات",
    "One-step CPVC solvent cement engineered for fast, permanent, leak-free joints in hot & cold water lines.":
      "لاصق CPVC كيميائي بخطوة واحدة مصمّم لوصلات سريعة ودائمة بلا تسريب في خطوط المياه الساخنة والباردة.",
    "Enquire Now": "استفسر الآن",
    "Request a Kit": "اطلب حزمة",
    "One Certified System. Complete Solution.": "منظومة واحدة معتمدة.. حلول متكاملة.",
    "Pipes and fittings engineered to work together as a single, reliable, leak-proof network — supplied with full documentation and BOQ alignment.":
      "تم تصميم الأنابيب والتوصيلات لتعمل معاً كشبكة موحدة وموثوقة خالية من التسريب — يتم توريدها مع كامل الوثائق الفنية ومطابقة جداول الكميات.",
    "FlowGuard® CPVC System": "نظام FlowGuard® CPVC",

    // Why
    "Why FlowGuard® CPVC": "لماذا نختار أنظمة FlowGuard® CPVC؟",
    "Built to Perform for 50+ Years": "مصمّم للأداء لأكثر من 50 عاماً",
    "The properties that make FlowGuard® the most trusted CPVC plumbing system in the world.":
      "الخصائص التي تجعل FlowGuard® نظام السباكة CPVC الأكثر ثقةً في العالم.",
    "High Temperature": "تحمّل الحرارة العالية",
    "Handles continuous hot water up to 93°C without softening, scaling or loss of pressure rating.":
      "يتحمّل المياه الساخنة المستمرة حتى 93°م دون ليونة أو ترسّبات أو فقدان لتصنيف الضغط.",
    "Non-Corrosive": "مقاومة التآكل",
    "Immune to internal and external corrosion, scaling and pitting — ideal for coastal and high-chlorine environments.":
      "مقاومة تامة للتآكل الداخلي والخارجي، الترسّبات، والتنقّر — مما يجعلها الخيار الأمثل للبيئات الساحلية وعالية الكلور.",
    "Fire-Retardant": "مقاوم للحريق",
    "Will not support combustion. Low flame-spread and smoke-generation make it safe for occupied buildings.":
      "لا يدعم الاحتراق. انتشار لهب وتوليد دخان منخفضان يجعلانه آمناً للمباني المأهولة.",
    "Safe Drinking Water": "مياه شرب آمنة",
    "Resists biofilm and chlorine dioxide. NSF-certified for potable water with no taste or odour transfer.":
      "مقاومة عالية لنشوء الأغشية الحيوية. معتمد من NSF لمياه الشرب لضمان عدم انتقال أي طعم أو رائحة.",
    "Easy Installation": "سهولة التركيب",
    "Lightweight with a fast, reliable solvent-cement joint — lower labour cost and quicker project handover.":
      "خفيف الوزن مع وصلة لاصق كيميائي سريعة وموثوقة — تكلفة عمالة أقل وتسليم أسرع للمشروع.",
    "50-Year Lifespan": "عمر افتراضي 50 عاماً",
    "A proven service life backed by a manufacturer warranty — long-term value with minimal maintenance.":
      "عمر خدمة مثبت مدعوم بضمان المصنّع — قيمة طويلة الأمد بأدنى صيانة.",

    // Stats band
    "Projects Supplied": "مشاريع تم توريدها",
    "Warranty": "سنوات الضمان",
    "Max Temperature": "درجة حرارة التشغيل",
    "Certified": "اعتمادات دولية",
    "Emirates Covered": "تغطية جغرافية",
    "Metres Installed": "متر تم تركيبه",
    "On-Spec Delivery": "تسليم مطابق للمواصفات",

    // Applications
    "Applications": "التطبيقات",
    "Trusted Across Every Sector": "موثوق في كل القطاعات",
    "From villas to towers and hospitals to industrial plants — wherever certified water piping matters.":
      "من الفلل إلى الأبراج ومن المستشفيات إلى المنشآت الصناعية — حيثما تهمّ أنابيب المياه المعتمدة.",
    "Residential": "سكني", "Villas & compounds": "فلل ومجمعات",
    "Commercial": "تجاري", "Towers & offices": "أبراج ومكاتب", "Towers & malls": "أبراج ومولات",
    "Hospitality": "ضيافة", "Hotels & education": "فنادق وتعليم", "Hotels & resorts": "فنادق ومنتجعات",
    "Industrial": "صناعي", "Utility & plants": "مرافق ومحطات", "Plants & utilities": "محطات ومرافق",

    // CTA
    "Partner With a Certified Supplier": "اعمل مع مورّد معتمد",
    "Compliance gaps delay projects. Get spec-ready FlowGuard® CPVC with full documentation, delivered across the UAE in 2–3 days.":
      "فجوات المطابقة تؤخّر المشاريع. احصل على FlowGuard® CPVC جاهز للمواصفات مع كامل الوثائق، يُسلَّم بالإمارات خلال 2–3 أيام.",
    "Call +971 54 769 6440": "اتصل \u202A+971 54 769 6440\u202C",

    // Footer
    "Al Waab Building Materials Trading EST — UAE supplier of certified FlowGuard® CPVC water piping systems. Delivered with confidence.":
      "مؤسسة الوعب لتجارة مواد البناء — مورّد إماراتي لأنظمة أنابيب مياه FlowGuard® CPVC المعتمدة. نُسلّمها بثقة.",
    "Quick Links": "روابط سريعة",
    "Project Kits": "حزم المشاريع",
    "Get in Touch": "تواصل معنا",
    "206 Aboo Baker, Al Siddique Road, Mohamed Hasan Building, Deira, Dubai, UAE":
      "206 شارع أبو بكر الصديق، مبنى محمد حسن، ديرة، دبي، الإمارات",
    "Al Waab Building Materials Trading EST. All rights reserved.": "مؤسسة الوعب لتجارة مواد البناء. جميع الحقوق محفوظة.",
    "FlowGuard® is a registered trademark of The Lubrizol Corporation. ·": "FlowGuard® علامة تجارية مسجّلة لشركة لوبريزول. ·",
    "Privacy": "الخصوصية", "Terms": "الشروط",

    // Page heroes / breadcrumbs
    "About Al Waab": "عن الوعب",
    "Certified water piping, delivered with confidence — the trusted FlowGuard® CPVC supplier in the United Arab Emirates.":
      "أنابيب مياه معتمدة، نُسلّمها بثقة — المورّد الموثوق لـ FlowGuard® CPVC في الإمارات العربية المتحدة.",
    "Genuine FlowGuard® CPVC pipes, fittings and project-ready kits — certified for hot & cold potable water across the UAE.":
      "أنابيب وتوصيلات FlowGuard® CPVC الأصلية وحزم المشاريع المتكاملة — معتمدة للمياه الساخنة والباردة الصالحة للشرب في كافة أنحاء الإمارات.",
    "FlowGuard® chlorinated PVC pipes — the global benchmark for safe, durable hot & cold potable water distribution.":
      "أنابيب FlowGuard® CPVC — المعيار العالمي لتوزيع آمن ومتين للمياه الساخنة والباردة الصالحة للشرب.",
    "A complete range of FlowGuard® CPVC fittings — precision-moulded for secure, leak-proof connections throughout the system.":
      "مجموعة كاملة من توصيلات FlowGuard® CPVC — مصبوبة بدقة لوصلات محكمة لا تسرّب في كامل النظام.",
    "Certified FlowGuard® CPVC piping trusted on landmark developments — from residential compounds to stadiums and infrastructure.":
      "أنابيب FlowGuard® CPVC المعتمدة موثوقة في مشاريع بارزة — من المجمعات السكنية إلى الملاعب والبنية التحتية.",
    "Specification-ready compliance. Every product we supply is backed by internationally recognised certification and local approvals.":
      "مطابقة جاهزة للمواصفات. كل منتج نورّده مدعوم بشهادات معترف بها دولياً واعتمادات محلية.",
    "Reliable supply, wherever your project is. Partner with Al Waab as a distributor or find your nearest supply point.":
      "توريد موثوق أينما كان مشروعك. كن شريكاً موزّعاً مع الوعب أو جد أقرب نقطة توريد.",
    "Contact Us": "اتصل بنا",
    "Request a quote, ask for documentation, or talk to our technical team. We respond within 24 hours.":
      "اطلب عرض سعر، أو اطلب الوثائق، أو تحدّث مع فريقنا الفني. نردّ خلال 24 ساعة.",

    // About page
    "Our Story": "قصتنا",
    "A Compliance-First Building Materials Partner": "شريك مواد بناء يضع المطابقة أولاً",
    "Al Waab Building Materials Trading EST is the United Arab Emirates branch of the Al Waab group — an organisation with a proven track record in certified plastic piping systems. We don't just supply pipes; we deliver safety, trust and certified performance.":
      "مؤسسة الوعب لتجارة مواد البناء هي فرع الإمارات العربية المتحدة لمجموعة الوعب — مؤسسة ذات سجل حافل في أنظمة الأنابيب البلاستيكية المعتمدة. نحن لا نورّد الأنابيب فحسب؛ بل نقدّم الأمان والثقة والأداء المعتمد.",
    "As an authorised supplier of FlowGuard® CPVC by The Lubrizol Corporation (USA), we provide consultants and contractors with genuine, traceable products, complete submittals and the technical backing needed to keep projects on schedule and fully compliant.":
      "بصفتنا مورّداً معتمداً لـ FlowGuard® CPVC من شركة لوبريزول (الولايات المتحدة)، نزوّد الاستشاريين والمقاولين بمنتجات أصلية قابلة للتتبّع، ومستندات كاملة، والدعم الفني اللازم لإبقاء المشاريع في موعدها ومطابقة تماماً.",
    "From villas and towers to hospitals and industrial plants, our certified piping systems are engineered to perform for 50 years and beyond.":
      "من الفلل والأبراج إلى المستشفيات والمنشآت الصناعية، صُمّمت أنظمة أنابيبنا المعتمدة للأداء 50 عاماً وأكثر.",
    "Talk to Our Team": "تحدّث مع فريقنا",
    "UAE": "الإمارات", "Dedicated Branch": "فرع مخصّص",
    "What Drives Us": "ما يحرّكنا",
    "Vision, Mission & Values": "الرؤية والرسالة والقيم",
    "Our Vision": "رؤيتنا",
    "To be the UAE's most trusted name in certified water piping — the partner specifiers turn to when compliance and long-term performance cannot be compromised.":
      "أن نكون الاسم الأكثر ثقةً في الإمارات بمجال أنابيب المياه المعتمدة — الشريك الذي يلجأ إليه المواصفون حين لا يُساوَم على المطابقة والأداء طويل الأمد.",
    "Our Mission": "رسالتنا",
    "To close compliance gaps and prevent project delays by delivering genuine FlowGuard® CPVC, full documentation and responsive technical support across the Emirates.":
      "سدّ فجوات المطابقة ومنع تأخّر المشاريع بتوريد FlowGuard® CPVC الأصلي والوثائق الكاملة والدعم الفني السريع في أنحاء الإمارات.",
    "Our Values": "قيمنا",
    "Integrity, certified quality and accountability. We stand behind every product with traceable certification and a manufacturer-backed warranty.":
      "النزاهة والجودة المعتمدة والمسؤولية. نقف خلف كل منتج بشهادة قابلة للتتبّع وضمان مدعوم من المصنّع.",
    "The Al Waab Advantage": "ميزة الوعب",
    "Partner With a Certified Supplier — Not Just a Vendor": "اعمل مع مورّد معتمد — وليس مجرّد بائع",
    "Everything we do is built to make your project safer, faster and fully approval-ready.":
      "كل ما نقوم به مصمّم لجعل مشروعك أكثر أماناً وأسرع وجاهزاً تماماً للاعتماد.",
    "Full Documentation": "وثائق كاملة",
    "EPD, HPD, submittals and approvals — ready to align with your BOQ and consultant requirements.":
      "EPD وHPD والمستندات والاعتمادات — جاهزة لمطابقة جدول الكميات ومتطلبات الاستشاري.",
    "Genuine & Certified": "أصلي ومعتمد",
    "Authentic FlowGuard® CPVC carrying WRAS, NSF and Dubai Municipality approvals.":
      "FlowGuard® CPVC أصلي يحمل اعتمادات WRAS وNSF وبلدية دبي.",
    "2–3 Day Delivery": "تسليم خلال 2–3 أيام",
    "Fast, reliable supply across the UAE so material availability never holds up your programme.":
      "توريد سريع وموثوق في الإمارات حتى لا يؤخّر توفّر المواد جدولك الزمني.",
    "Expert Support": "دعم الخبراء",
    "Technical demonstrations, installation guidance and consultant support from start to handover.":
      "عروض فنية وإرشادات تركيب ودعم استشاري من البداية حتى التسليم.",
    "Let's Build Something That Lasts": "لنبنِ شيئاً يدوم",
    "Reach out for product specifications, certifications or a project quotation.":
      "تواصل معنا للحصول على مواصفات المنتجات أو الشهادات أو عرض سعر للمشروع.",
    "View Products": "عرض المنتجات",

    // Specs / tables (labels)
    "Specifications": "المواصفات",
    "Technical Overview": "نظرة فنية عامة",
    "Property": "الخاصية",
    "FlowGuard® CPVC Specification": "مواصفات FlowGuard® CPVC",
    "Specification": "المواصفة",
    "Material": "المادة",
    "Standard": "المعيار",
    "Sizes": "الأقطار والمقاسات",
    "Available Sizes": "المقاسات المتوفرة",
    "Max. Working Temperature": "أقصى حرارة تشغيل",
    "Max. Temperature": "أقصى حرارة",
    "Pressure Rating": "تصنيف الضغط",
    "Jointing": "طريقة الوصل",
    "Jointing Method": "طريقة الوصل",
    "Approvals": "الاعتمادات",
    "Dimension Ratio": "نسبة الأبعاد",
    "Colour": "اللون",
    "Types": "الأنواع",
    // Mapping for products from E:\alwaab\protect
    "Pipes PN16": "أنابيب PN16 (ضغط 16 بار)",
    "Pipes PN20": "أنابيب PN20 عالية الضغط (20 بار)",
    "Elbow 90": "كوع 90 درجة (زاوية قائمة)",
    "Elbow 45": "كوع 45 درجة",
    "Equal Tee": "وصلة ثلاثية (Tee) متساوية",
    "Reducing Tee": "وصلة ثلاثية (Tee) مخفضة",
    "Coupler": "وصلة مستقيمة (Coupler)",
    "End Cap": "سدادة نهاية (End Cap)",
    "Ball Valve": "محبس كروي (Ball Valve) احترافي",
    "Union": "وصلة تجميع وتفكيك (Union)",
    "Male Adapter": "مهايئ ذكر لولبي (Male Adapter)",
    "Female Adapter": "مهايئ أنثى لولبي (Female Adapter)",
    "Add to Quote": "أضف لطلب السعر",
    "Added": "تمت الإضافة",
    "All": "الكل",
    "Pipes": "الأنابيب",
    "Fittings": "التوصيلات",
    "Valves": "المحابس",
    "Flanges": "الفلنجات",
    "Threaded": "لولبي",
    "Brass": "النحاس",
    "Accessories": "الملحقات",
    "Official Schedule of Items": "جدول الأصناف الرسمي",
    "Coupler": "وصلة مستقيمة",
    "Tee 90°": "وصلة ثلاثية 90°",
    "Clips": "مشابك تثبيت",
    "Stop Valve": "محبس إيقاف",
    "Wall Mounting Elbow": "كوع تثبيت حائط",
    "Female Threaded Tee": "وصلة ثلاثية لولبية أنثى",
    "Brass Male Union": "اتحاد نحاسي ذكر",
    "Brass Elbow": "كوع نحاسي",
    "uPVC Male Plug": "سدادة uPVC ذكر",
    "CPVC Solvent Cement": "لاصق CPVC كيميائي",
    "E-Z Weld 786™ CPVC heavy-body orange cement — medium set, industrial strength. Meets ASTM D2846 & F493. ½ pint (237ml). Part code: SLCEM250.":
      "لاصق E-Z Weld 786™ CPVC برتقالي ثقيل — تجفيف متوسط، قوة صناعية. مطابق ASTM D2846 و F493. نصف pint (237مل). رمز القطعة: SLCEM250.",
    "CPVC Pipe S6.3 — PN16": "أنبوب CPVC S6.3 — PN16",
    "CPVC Pipe S5 — PN20": "أنبوب CPVC S5 — PN20",
    "Double Union Ball Valve": "محبس كروي باتحاد مزدوج",
    "CPVC Male Threaded Adapter": "مهايئ CPVC لولبي ذكر",
    "CPVC Female Threaded Adapter": "مهايئ CPVC لولبي أنثى",
    "Brass Male Threaded Adapter": "مهايئ نحاسي لولبي ذكر",
    "Brass Female Threaded Adapter": "مهايئ نحاسي لولبي أنثى",
    "Search products...": "ابحث عن منتج...",
    "My Quote Request": "طلب السعر الخاص بي",
    "Add products below to build your quote": "أضف المنتجات أدناه لبناء طلب عرض السعر",
    "product(s) in your quote": "منتج/منتجات في طلبك",
    "items selected": "عناصر مختارة",
    "Need a Project Quotation?": "تحتاج عرض سعر لمشروع؟",
    "Send us your BOQ and we'll return a spec-ready FlowGuard® CPVC package with full documentation.":
      "يرجى تزويدنا بجداول الكميات (BOQ) وسنقوم بإعداد عرض سعر فني متكامل لأنظمة FlowGuard® CPVC مع كافة الوثائق المطلوبة.",
    "Call Us": "اتصل بنا",

    // CPVC pipes page
    "Engineered for a 50-Year Lifespan": "مصمّم لعمر افتراضي 50 عاماً",
    "FlowGuard® CPVC pipes carry potable water safely at high temperatures without corroding, scaling or supporting bacterial growth. Manufactured from premium Lubrizol compound, they are the most specified CPVC plumbing pipes in the world.":
      "تنقل أنابيب FlowGuard® CPVC مياه الشرب بأمان عند درجات حرارة عالية دون تآكل أو ترسّبات أو دعم لنمو البكتيريا. مصنوعة من خامة لوبريزول الممتازة، وهي أكثر أنابيب سباكة CPVC تحديداً في المواصفات حول العالم.",
    "Lightweight and easy to install with a simple solvent-cement joint, they reduce labour time and total installed cost while delivering decades of reliable service.":
      "خفيفة وسهلة التركيب بوصلة لاصق كيميائي بسيطة، تقلّل زمن العمالة والتكلفة الإجمالية للتركيب مع عقود من الخدمة الموثوقة.",
    "Why Specify FlowGuard® Pipes": "لماذا تختار أنابيب FlowGuard®",
    "Key Benefits": "أبرز المزايا",
    "Hot Water Ready": "جاهز للمياه الساخنة",
    "Maintains pressure rating with continuous hot water up to 93°C.": "يحافظ على تصنيف الضغط مع مياه ساخنة مستمرة حتى 93°م.",
    "Corrosion-Free": "خالٍ من التآكل",
    "No rust, scale or pitting — perfect for the UAE's coastal climate.": "لا صدأ ولا ترسّبات ولا تنقّر — مثالي لمناخ الإمارات الساحلي.",
    "Safe Water": "مياه آمنة",
    "NSF-certified, biofilm-resistant, no taste or odour transfer.": "معتمد NSF، مقاوم للأغشية الحيوية، بلا نقل طعم أو رائحة.",
    "Will not sustain combustion; low smoke generation.": "لا يديم الاحتراق؛ توليد دخان منخفض.",
    "Pipe Specifications": "مواصفات الأنابيب",
    "Order FlowGuard® CPVC Pipes": "اطلب أنابيب FlowGuard® CPVC",
    "Fast 2–3 day delivery across the UAE with full certification.": "تسليم سريع خلال 2–3 أيام في الإمارات مع شهادات كاملة.",
    "Get a Quote": "احصل على عرض سعر",
    "View Fittings": "عرض التوصيلات",
    "View Pipes": "عرض الأنابيب",

    // CPVC fittings page
    "Built to Match Every Pipe Run": "مصمّمة لتطابق كل خط أنابيب",
    "From simple direction changes to complex manifolds, our fittings are engineered to the same exacting standard as FlowGuard® pipes — ensuring a homogeneous, fully compatible piping system with no weak points.":
      "من تغييرات الاتجاه البسيطة إلى المشعّبات المعقّدة، تُصنع توصيلاتنا بنفس المعيار الدقيق لأنابيب FlowGuard® — لضمان نظام أنابيب متجانس ومتوافق تماماً بلا نقاط ضعف.",
    "Every fitting forms a permanent solvent-cement bond, delivering joints that are stronger than the pipe itself and proven to last the lifetime of the installation.":
      "تشكّل كل توصيلة رابطة لاصق كيميائي دائمة، فتمنح وصلات أقوى من الأنبوب نفسه ومثبت أنها تدوم طوال عمر التركيب.",
    "Range": "النطاق",
    "Full Fittings Range": "نطاق التوصيلات الكامل",
    "Everything needed to complete a FlowGuard® CPVC network — available in all matching sizes.":
      "كل ما يلزم لإكمال شبكة FlowGuard® CPVC — متوفر بكل المقاسات المتطابقة.",
    "Elbows & Bends": "أكواع وانحناءات",
    "45° and 90° elbows for smooth, low-loss direction changes.": "أكواع 45° و90° لتغييرات اتجاه سلسة وقليلة الفقد.",
    "Tees & Reducing Tees": "تيهات وتيهات مخفّضة",
    "Equal and reducing tees for branching distribution lines.": "تيهات متساوية ومخفّضة لتفريع خطوط التوزيع.",
    "Couplings & Unions": "وصلات وكَلبسات",
    "Straight couplings, reducers and unions for clean joins and maintenance.": "وصلات مستقيمة ومخفّضات وكلبسات لوصلات نظيفة وصيانة سهلة.",
    "Valves & Adapters": "محابس ومهايئات",
    "Ball valves and male/female threaded adapters for transitions.": "محابس كروية ومهايئات لولبية ذكر/أنثى للانتقالات.",
    "Fitting Specifications": "مواصفات التوصيلات",
    "Complete Your System": "أكمل نظامك",
    "Order pipes and fittings together as one certified package.": "اطلب الأنابيب والتوصيلات معاً كحزمة معتمدة واحدة.",

    // Projects page
    "Selected Work": "أعمال مختارة",
    "Our Portfolio": "معرض أعمالنا",
    "Featured Projects": "المشاريع المميزة",
    "Landmark developments supplied with certified FlowGuard® CPVC piping systems — click any project to view the full image.":
      "مشاريع بارزة زُوّدت بأنظمة أنابيب FlowGuard® CPVC المعتمدة — انقر على أي مشروع لعرض الصورة كاملة.",
    "18 Projects": "18 مشروعاً",
    "Projects": "مشاريع",
    "We're always looking for new projects to work on. Explore landmark developments across Qatar and the region where FlowGuard® CPVC systems have been supplied.":
      "نبحث دائماً عن مشاريع جديدة. استكشف المشاريع البارزة في قطر والمنطقة التي زُوّدت بأنظمة FlowGuard® CPVC.",
    "Where Our Piping Performs": "حيث تتألق أنابيبنا",
    "A snapshot of the sectors and landmark developments served by the Al Waab group's certified CPVC systems.":
      "لمحة عن القطاعات والمشاريع البارزة التي خدمتها أنظمة CPVC المعتمدة لمجموعة الوعب.",
    "Doha Marriot": "فندق ماريوت الدوحة",
    "Doha Expo": "معرض الدوحة",
    "Ashgal Health Centres": "مراكز صحية أشغال",
    "Bus Station": "محطة حافلات",
    "Gewan Island": "جزيرة جوان",
    "La plage south pearl qatar": "لا بلاج ساوث بيرل قطر",
    "National health lab": "المختبر الوطني للصحة",
    "Katara Tower - lusail-marina": "برج كتارا - لوسيل مارينا",
    "qatar foundation stadium": "ملعب مؤسسة قطر",
    "al rayyan stadium": "ملعب الريان",
    "3170 Affordable Villa": "3170 فيلا اقتصادية",
    "Health Centre Mesaieed": "مركز صحي مسيعيد",
    "al maha center": "مركز المها",
    "ISF Duhail Camp": "معسكر ISF دحيل",
    "Residential Buildings Fox Hills": "مباني سكنية فوكس هيلز",
    "Kahramaa Customer Centre Thumama": "مركز كهرماء للعملاء الثمامة",
    "Mega Reservoir Umm Salal": "خزان مياه ضخم أم صلال",
    "Al Furjan Markets": "أسواق الفرجان",
    "Commercial Tower": "برج تجاري", "High-Rise Development": "مشروع شاهق",
    "Sports & Leisure": "رياضة وترفيه", "Stadium Facilities": "مرافق ملاعب",
    "Villa Communities": "مجمعات فلل",
    "Infrastructure": "بنية تحتية", "Utilities & Roads": "مرافق وطرق",
    "Process & Plant": "عمليات ومحطات",
    "Healthcare": "رعاية صحية", "Hospitals & Clinics": "مستشفيات وعيادات",
    "Sectors We Serve": "القطاعات التي نخدمها",
    "Built for Every Application": "مصمّم لكل تطبيق",
    "Have a Project in Mind?": "لديك مشروع؟",
    "From a single villa to a tower — get certified CPVC supplied on time and on spec.":
      "من فيلا واحدة إلى برج — احصل على CPVC معتمد يُورّد في الوقت ووفق المواصفات.",
    "Start a Conversation": "ابدأ محادثة",

    // Certificates page
    "Compliance": "المطابقة",
    "Certified, Approved & Traceable": "معتمد وموثّق وقابل للتتبّع",
    "FlowGuard® CPVC supplied by Al Waab meets the requirements of leading international and UAE authorities.":
      "يفي FlowGuard® CPVC المورّد من الوعب بمتطلبات أبرز الجهات الدولية والإماراتية.",
    "NSF International": "NSF الدولية", "Potable water certification": "اعتماد مياه الشرب",
    "WRAS Approved": "معتمد WRAS", "UK water regulations": "لوائح المياه البريطانية",
    "Gulf Green Mark": "علامة الخليج الخضراء", "Sustainability rated": "مصنّف للاستدامة",
    "Genuine Lubrizol compound": "خامة لوبريزول الأصلية",
    "Lubrizol, USA": "لوبريزول، الولايات المتحدة", "Authorised supplier": "مورّد معتمد",
    "Documentation": "الوثائق",
    "Approvals at a Glance": "الاعتمادات بنظرة سريعة",
    "Approval / Document": "الاعتماد / الوثيقة", "Scope": "النطاق",
    "Need Documentation for Submittal?": "تحتاج وثائق للتقديم؟",
    "We provide complete certificates, EPD/HPD and submittal packs aligned to your consultant's requirements.":
      "نوفّر شهادات كاملة وEPD/HPD وحزم تقديم مطابقة لمتطلبات استشاريك.",
    "Request Documents": "اطلب الوثائق",
    "Official Documents": "الوثائق الرسمية",
    "Third-Party Certification & Lab Test Reports": "شهادات الطرف الثالث وتقارير المختبر",
    "Pre-qualification documentation for domestic water systems — each certificate is independently issued and traceable.":
      "وثائق تأهيل مسبق لأنظمة المياه المنزلية — كل شهادة صادرة بشكل مستقل وقابلة للتتبّع.",
    "Pre-qualification documentation for domestic water systems — 10 independently issued certificates, fully traceable.":
      "وثائق تأهيل مسبق لأنظمة المياه المنزلية — 10 شهادات صادرة بشكل مستقل وقابلة للتتبّع بالكامل.",
    "Pre-Qualification Pack": "حزمة التأهيل المسبق",
    "Third Party Certification & Lab Test Report": "تقرير شهادات الطرف الثالث والمختبر",
    "Complete domestic water pre-qualification dossier covering WRAS, NSF, HPD, LEED and Dubai Municipality compliance references.":
      "ملف تأهيل مسبق كامل لمياه المنازل يشمل مراجع الامتثال لـ WRAS وNSF وHPD وLEED وبلدية دبي.",
    "Domestic Potable Water Systems": "أنظمة مياه الشرب المنزلية",
    "View Certificate": "عرض الشهادة",
    "Download PDF": "تحميل PDF",
    "Download Full PDF": "تحميل PDF الكامل",
    "WRAS Approved Material": "مادة معتمدة WRAS",
    "Official Water Regulations Approval Scheme material approval for tan-coloured extruded CPVC pipe for wholesome water up to 85°C.":
      "اعتماد رسمي من Water Regulations Approval Scheme لمادة أنبوب CPVC بيجي مبثوق لمياه الشرب حتى 85°م.",
    "Approval No.": "رقم الاعتماد",
    "Valid": "الصلاحية",
    "Standard": "المعيار",
    "Holder": "الجهة الحاملة",
    "Al Waab Plastics Factory": "مصنع الوعب للبلاستيك",
    "NSF Certified": "معتمد NSF",
    "NSF-listed CPVC pipe certified for potable water with NSF/ANSI 61 health effects up to Commercial Hot (180°F) and NSF/ANSI 372 lead-free compliance.":
      "أنبوب CPVC مدرج في NSF معتمد لمياه الشرب وفق NSF/ANSI 61 حتى الماء الساخن التجاري (180°ف) وامتثال NSF/ANSI 372 للخلو من الرصاص.",
    "Product": "المنتج",
    "Material": "المادة",
    "Facility": "المنشأة",
    "Standards": "المعايير",
    "Lubrizol Technical": "تقني لوبريزول",
    "Official Lubrizol / FlowGuard® technical response confirming CPVC suitability for potable water treated with copper/silver ionization sanitation methods.":
      "رد تقني رسمي من لوبريزول / FlowGuard® يؤكد ملاءمة CPVC لمياه الشرب المعالجة بطرق التعقيم بالأيونات النحاسية/الفضية.",
    "Date": "التاريخ",
    "Prepared by": "أُعدّ بواسطة",
    "Issuer": "الجهة المصدرة",
    "Conclusion": "النتيجة",
    "Suitable for all sanitation methods": "مناسب لجميع طرق التعقيم",
    "View": "عرض",
    "WRAS Material Approval — FlowGuard™ CPVC": "اعتماد مادة WRAS — FlowGuard™ CPVC",
    "NSF/ANSI 14 & 61 — CPVC Potable Water Pipe": "NSF/ANSI 14 و 61 — أنبوب CPVC لمياه الشرب",
    "Chemical Resistance — Copper/Silver Ionization": "مقاومة كيميائية — التعقيم بالأيونات النحاسية/الفضية",
    "WRAS Material Approval 2112532": "اعتماد مادة WRAS رقم 2112532",
    "FlowGuard™ CPVC pipe — BS 6920 potable water compliance (valid to Dec 2026)": "أنبوب FlowGuard™ CPVC — امتثال BS 6920 لمياه الشرب (صالح حتى ديسمبر 2026)",
    "AL WAAB Pipe SE 16558 — CPVC potable water, Commercial Hot rated": "أنبوب AL WAAB SE 16558 — CPVC لمياه الشرب، مُصنّف للماء الساخن التجاري",
    "Lead-free plumbing compliance (US Safe Drinking Water Act)": "امتثال السباكة الخالية من الرصاص (قانون مياه الشرب الأمريكي)",
    "Health Product Declaration & green building recognition": "إقرار المنتج الصحي والاعتراف بالمباني الخضراء",
    "Lubrizol Technical Letter": "خطاب تقني من لوبريزول",
    "CPVC chemical resistance — copper/silver ionization (May 2025)": "مقاومة CPVC الكيميائية — التعقيم بالأيونات النحاسية/الفضية (مايو 2025)",
    "10 independently issued certificates, fully traceable.": "10 شهادات صادرة بشكل مستقل وقابلة للتتبّع بالكامل.",
    "10 Official Certificates": "10 شهادات رسمية",
    "EPD Certified": "معتمد EPD",
    "Environmental Product Declaration (EPD)": "إقرار المنتج البيئي (EPD)",
    "IGM Environmental Product Declaration for FlowGuard CPVC pipes per EN 15804:2013 and IGM Product Category Rules V2.0.":
      "إقرار المنتج البيئي IGM لأنابيب FlowGuard CPVC وفق EN 15804:2013 وقواعد فئات منتجات IGM V2.0.",
    "Certificate No.": "رقم الشهادة",
    "Al Waab Plastics Qatar": "الوعب للبلاستيك قطر",
    "NSF/ANSI/CAN 61 — FlowGuard Fittings": "NSF/ANSI/CAN 61 — توصيلات FlowGuard",
    "NSF product listing for Alwaab FlowGuard CPVC fittings — drinking water system components, Commercial Hot rated.":
      "قائمة منتجات NSF لتوصيلات FlowGuard CPVC من الوعب — مكونات أنظمة مياه الشرب، مُصنّفة للماء الساخن التجاري.",
    "Size Range": "نطاق المقاسات",
    "Listed": "تاريخ الإدراج",
    "HPD Verified": "HPD موثّق",
    "Health Product Declaration (HPD) v3.0": "إقرار المنتج الصحي (HPD) الإصدار 3.0",
    "Third-party verified Health Product Declaration for FlowGuard CPVC pipes and fittings — transparency for building material health impacts.":
      "إقرار منتج صحي موثّق من طرف ثالث لأنابيب وتوصيلات FlowGuard CPVC — شفافية التأثيرات الصحية لمواد البناء.",
    "Published": "تاريخ النشر",
    "Expiry": "تاريخ الانتهاء",
    "Verifier": "الجهة المُوثّقة",
    "FlowGuard CPVC Pipes & Fittings": "أنابيب وتوصيلات FlowGuard CPVC",
    "ISO 9001": "ISO 9001",
    "ISO 14001": "ISO 14001",
    "ISO 45001": "ISO 45001",
    "ISO 9001:2015 Quality Management": "ISO 9001:2015 إدارة الجودة",
    "International Certification Authority (ICA) certificate for quality management system covering manufacturing of CPVC, uPVC and PVC pipes and fittings.":
      "شهادة الهيئة الدولية للاعتماد (ICA) لنظام إدارة الجودة في تصنيع أنابيب وتوصيلات CPVC وuPVC وPVC.",
    "Issue Date": "تاريخ الإصدار",
    "ISO 14001:2015 Environmental Management": "ISO 14001:2015 الإدارة البيئية",
    "ICA environmental management system certification for Al Waab Plastics Factory manufacturing operations.":
      "اعتماد نظام الإدارة البيئية ICA لعمليات تصنيع مصنع الوعب للبلاستيك.",
    "ISO 45001:2018 Occupational Health & Safety": "ISO 45001:2018 الصحة والسلامة المهنية",
    "ICA occupational health and safety management system certification for pipe and fittings manufacturing facility.":
      "اعتماد نظام إدارة الصحة والسلامة المهنية ICA لمنشأة تصنيع الأنابيب والتوصيلات.",

    // Distributors page
    "Coverage": "التغطية",
    "Supplying Across the Emirates": "نورّد في كل الإمارات",
    "Our UAE branch delivers genuine FlowGuard® CPVC to projects in every emirate within 2–3 days.":
      "يوصّل فرعنا في الإمارات FlowGuard® CPVC الأصلي لمشاريع كل إمارة خلال 2–3 أيام.",
    "Dubai": "دبي", "Head office & main distribution hub for the UAE.": "المكتب الرئيسي ومركز التوزيع الأساسي للإمارات.",
    "Abu Dhabi": "أبوظبي", "Serving the capital's residential, commercial & government projects.": "نخدم مشاريع العاصمة السكنية والتجارية والحكومية.",
    "Sharjah & Northern Emirates": "الشارقة والإمارات الشمالية",
    "Fast delivery to Sharjah, Ajman, RAK, Fujairah & UAQ.": "توصيل سريع إلى الشارقة وعجمان ورأس الخيمة والفجيرة وأم القيوين.",
    "Become a Partner": "كن شريكاً",
    "Join the Al Waab Distributor Network": "انضم لشبكة موزّعي الوعب",
    "Are you a building materials trader, MEP contractor or plumbing wholesaler? Partner with us to offer your customers genuine, certified FlowGuard® CPVC backed by full technical support.":
      "هل أنت تاجر مواد بناء أو مقاول MEP أو تاجر جملة سباكة؟ كن شريكاً لنا لتقدّم لعملائك FlowGuard® CPVC أصلياً ومعتمداً مدعوماً بدعم فني كامل.",
    "Competitive trade pricing": "أسعار جملة تنافسية",
    "Marketing & technical support": "دعم تسويقي وفني",
    "Reliable stock availability": "توفّر مخزون موثوق",
    "Full certification packs": "حزم شهادات كاملة",
    "Apply to Partner": "تقدّم لتكون شريكاً",

    // Contact page
    "Al Waab Building Materials Trading EST": "مؤسسة الوعب لتجارة مواد البناء",
    "Address": "العنوان", "Phone": "الهاتف", "Email": "البريد الإلكتروني",
    "Working Hours": "ساعات العمل",
    "Sunday – Thursday: 8:00 AM – 6:00 PM": "الأحد – الخميس: 8:00 ص – 6:00 م",
    "Full Name": "الاسم الكامل", "Company": "الشركة",
    "Product of Interest": "المنتج المطلوب",
    "Project Details / Message": "تفاصيل المشروع / الرسالة",
    "FlowGuard® CPVC Pipes": "أنابيب FlowGuard® CPVC",
    "FlowGuard® CPVC Fittings": "توصيلات FlowGuard® CPVC",
    "Project-Ready Kit": "حزمة جاهزة للمشروع",
    "Solvent Cement & Accessories": "لاصق كيميائي وملحقات",
    "Documentation / Certificates": "وثائق / شهادات",
    "Send Request": "إرسال الطلب",

    // Generic
    "FlowGuard®": "FlowGuard®", "Kits": "حزم", "Accessory": "ملحق"
  };

  /* ---------------- PRODUCT CATALOG (Linked to protect folder) ---------------- */
  var PRODUCTS_CATALOG = [
    { id: "p16", name: "Pipes PN16", img: "protect/Pipes PN16.jpg" },
    { id: "p20", name: "Pipes PN20", img: "protect/Pipes PN20.jpg" },
    { id: "e90", name: "Elbow 90", img: "protect/Elbow 90.jpg" },
    { id: "e45", name: "Elbow 45", img: "protect/Elbow 45.jpg" },
    { id: "et", name: "Equal Tee", img: "protect/Equal Tee.jpg" },
    { id: "rt", name: "Reducing Tee", img: "protect/Reducing Tee.jpg" },
    { id: "cp", name: "Coupler", img: "protect/Coupler.jpg" },
    { id: "ec", name: "End Cap", img: "protect/End Cap.jpg" },
    { id: "bv", name: "Ball Valve", img: "protect/Ball Valve.jpg" },
    { id: "un", name: "Union", img: "protect/Union.jpg" },
    { id: "ma", name: "Male Adapter", img: "protect/Male Adapter.jpg" },
    { id: "fa", name: "Female Adapter", img: "protect/Female Adapter.jpg" }
  ];

  var selectedProducts = new Set();

  function renderCatalog() {
    var grid = document.getElementById("productGrid");
    if (!grid) return;
    grid.innerHTML = "";
    PRODUCTS_CATALOG.forEach(function (p) {
      var card = document.createElement("div");
      // إضافة class 'in' مباشرة إذا كان العنصر برمجياً أو تركه لـ main.js ليظهره
      card.className = "product-card reveal in"; 
      card.innerHTML = 
        '<div class="p-img"><img src="images/' + p.img + '" alt="' + p.name + '" loading="lazy"></div>' +
        '<div class="p-info"><h3 data-noi18n="false">' + p.name + '</h3>' +
        '<button class="btn-add" data-id="' + p.id + '">' + (selectedProducts.has(p.id) ? "Added" : "Add to Quote") + '</button></div>';
      grid.appendChild(card);
    });

    grid.querySelectorAll(".btn-add").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = this.getAttribute("data-id");
        if (selectedProducts.has(id)) {
          selectedProducts.delete(id);
          this.textContent = curLang === "ar" ? "أضف لطلب السعر" : "Add to Quote";
          this.classList.remove("active");
        } else {
          selectedProducts.add(id);
          this.textContent = curLang === "ar" ? "تمت الإضافة" : "Added";
          this.classList.add("active");
        }
        updateQuoteBadge();
      });
    });
    if (typeof walkTranslate === "function") walkTranslate(curLang);
  }

  function removeLegacyQuoteBadge() {
    var badge = document.getElementById("quoteBadge");
    if (badge) badge.parentNode.removeChild(badge);
  }

  function updateQuoteBadge() {
    // Products page uses the top quote bar — no legacy bottom-left badge
    if (document.getElementById("quoteTopBar") || document.getElementById("quoteCartBtn")) {
      removeLegacyQuoteBadge();
      return;
    }

    var badge = document.getElementById("quoteBadge");
    if (!badge) {
      badge = document.createElement("button");
      badge.id = "quoteBadge";
      badge.className = "quote-badge";
      document.body.appendChild(badge);
      badge.onclick = function() { window.location.href = "contact.html?items=" + Array.from(selectedProducts).join(","); };
    }
    badge.style.display = selectedProducts.size > 0 ? "flex" : "none";
    var label = curLang === "ar" ? " طلب السعر: " : " My Quote: ";
    badge.innerHTML = '📂 ' + label + selectedProducts.size + ' ' + (curLang === "ar" ? "أصناف" : "items");
  }

  // Placeholder attribute translations
  var PH = {
    "Your name": "اسمك",
    "Company name": "اسم الشركة",
    "you@email.com": "you@email.com",
    "+971 ...": "\u202A+971 ...\u202C",
    "Tell us about your project, quantities or BOQ...": "أخبرنا عن مشروعك أو الكميات أو جدول الكميات...",
    "Ask about temperature, pressure, sizes, CPVC vs PPR...": "اسأل عن الحرارة أو الضغط أو المقاسات أو مقارنة CPVC بـ PPR...",
    "Search products...": "ابحث عن منتج..."
  };

  /* ---------------- i18n ENGINE ---------------- */
  var orig = new WeakMap();
  function norm(s) { return s.replace(/[\u00A0\u200B]/g, " ").replace(/\s+/g, " ").trim(); }

  function walkTranslate(lang) {
    var tw = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode: function (n) {
        if (!n.nodeValue || !n.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
        var p = n.parentNode;
        while (p) {
          if (p.nodeType === 1) {
            if (p.hasAttribute && p.hasAttribute("data-noi18n")) return NodeFilter.FILTER_REJECT;
            var t = p.tagName;
            if (t === "SCRIPT" || t === "STYLE") return NodeFilter.FILTER_REJECT;
          }
          p = p.parentNode;
        }
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    var nodes = [], n;
    while ((n = tw.nextNode())) nodes.push(n);
    nodes.forEach(function (node) {
      if (!orig.has(node)) orig.set(node, node.nodeValue);
      var o = orig.get(node);
      
      if (lang === "en") {
        node.nodeValue = o;
        return;
      }
      
      var val = DICT[norm(o)];
      if (val) {
        var lead = (o.match(/^[\s\u00A0]*/) || [""])[0];
        var trail = (o.match(/\s*$/) || [""])[0];
        node.nodeValue = lead + val + trail;
      } else { node.nodeValue = o; }
    });
  }

  function translateAttrs(lang) {
    document.querySelectorAll("[placeholder], [title]").forEach(function (el) {
      if (!el._oph) el._oph = el.getAttribute("placeholder");
      if (el._oph) el.setAttribute("placeholder", lang === "ar" ? (PH[norm(el._oph)] || el._oph) : el._oph);
    });
  }

  // تحديد اللغة الافتراضية بناءً على المتصفح أو الاختيار السابق
  var savedLang = localStorage.getItem("lang");
  var curLang = savedLang ? savedLang : (navigator.language.startsWith("ar") ? "ar" : "en");

  function setLang(lang) {
    curLang = lang;
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";
    walkTranslate(lang);
    translateAttrs(lang);
    localStorage.setItem("lang", lang);
    if (langBtn) langBtn.querySelector(".lbl").textContent = lang === "ar" ? "English" : "العربية";
    updateBotChrome();
    if (document.getElementById("productFilters")) applyProductFilters();
    if (document.getElementById("quoteTopBar")) updateQuoteCartUI();
  }

  /* ---------------- Language toggle button ---------------- */
  var langBtn = null;
  function buildLangBtn() {
    langBtn = document.createElement("button");
    langBtn.className = "lang-btn";
    langBtn.setAttribute("data-noi18n", "");
    langBtn.setAttribute("aria-label", "Switch language");
    langBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg><span class="lbl">العربية</span>';
    document.body.appendChild(langBtn);
    langBtn.addEventListener("click", function () { setLang(curLang === "ar" ? "en" : "ar"); });
  }

  /* =================================================================
     AI PRODUCT ASSISTANT  —  knowledge base from the CPVC catalogue
  ================================================================= */
  var KB = [
    { k: ["products","list","items","available","catalog","المنتجات","الأصناف","قائمة","متوفر"],
      en: "📦 **Our Product Range**\nWe supply a full range of FlowGuard® CPVC including:\n• Pipes (PN16/PN20)\n• Elbows (90°/45°)\n• Tees (Equal/Reducing)\n• Couplers & Unions\n• Ball Valves & Adapters\nWould you like a quote for specific items from our catalog?",
      ar: "📦 **مجموعة منتجاتنا**\nنوفر تشكيلة كاملة من أنظمة FlowGuard® CPVC تشمل:\n• الأنابيب (PN16/PN20)\n• الأكواع (90°/45°)\n• التيهات (متساوية/مخفضة)\n• الوصلات واليونين\n• المحابس والمهايئات\nهل ترغب في الحصول على عرض سعر لأصناف محددة من الكتالوج الخاص بنا؟" },

    { k: ["temperature","hot","heat","°c","degree","how hot","حرارة","حراره","ساخن","درجة","تتحمل","سخونة"],
      en: "🌡️ **Temperature performance**\nFlowGuard® CPVC handles continuous hot water up to **93°C** without softening or losing its pressure rating.\n• Vicat softening point: ≥110°C (pipe), ≥103°C (fitting)\n• Flash-ignition temperature: 480°C (vs only 340°C for PPR)\nThis makes it ideal for hot & cold potable water in the UAE climate.",
      ar: "🌡️ **الأداء الحراري**\nيتحمّل FlowGuard® CPVC المياه الساخنة المستمرة حتى **93°م** دون ليونة أو فقدان لتصنيف الضغط.\n• نقطة ليونة فيكات: ≥110°م (للأنبوب)، ≥103°م (للتوصيلة)\n• درجة الاشتعال الوميضي: 480°م (مقابل 340°م فقط لـ PPR)\nمما يجعله مثالياً للمياه الساخنة والباردة الصالحة للشرب في مناخ الإمارات." },

    { k: ["pressure","bar","pn16","pn20","pn 16","pn 20","rating","ضغط","بار"],
      en: "📊 **Pressure ratings (PN 16 & PN 20)**\nWorking pressure vs temperature:\n• 20°C → 16 bar (PN16) / 20 bar (PN20)\n• 40°C → 11 / 14 bar\n• 60°C → 6 / 8 bar\n• 80°C → 4 / 5 bar\n• 95°C → 2 / 3 bar\nAll sizes 16–160 mm are available in PN 16 and PN 20.",
      ar: "📊 **تصنيفات الضغط (PN 16 و PN 20)**\nضغط التشغيل مقابل الحرارة:\n• 20°م → 16 بار (PN16) / 20 بار (PN20)\n• 40°م → 11 / 14 بار\n• 60°م → 6 / 8 بار\n• 80°م → 4 / 5 بار\n• 95°م → 2 / 3 بار\nكل المقاسات 16–160 مم متوفرة بـ PN 16 و PN 20." },

    { k: ["size","sizes","diameter","mm","dn","inch","مقاس","مقاسات","قطر","قياس","حجم"],
      en: "📏 **Available sizes**\nPipes: **16, 20, 25, 32, 40, 50, 63, 75, 90, 110, 160 mm** — in PN 16 & PN 20.\nFittings cover the full matching range (16 mm up to 160 mm) including couplers, elbows, tees, reducers, valves, flanges and brass transitions.",
      ar: "📏 **المقاسات المتوفرة**\nالأنابيب: **16، 20، 25، 32، 40، 50، 63، 75، 90، 110، 160 مم** — بـ PN 16 و PN 20.\nتغطّي التوصيلات النطاق المطابق كاملاً (16 إلى 160 مم) وتشمل الوصلات والأكواع والتيهات والمخفّضات والمحابس والفلنجات والمهايئات النحاسية." },

    { k: ["ppr","vs ppr","compare ppr","cpvc or ppr","مقارنة ppr","بي بي ار","البولي بروبلين"],
      en: "⚖️ **CPVC vs PPR — why CPVC wins**\n• Tensile strength: CPVC **50 MPa** vs PPR 30 MPa\n• Limiting Oxygen Index: **60** vs 17 (far safer in fire)\n• Flash ignition: 480°C vs 340°C\n• Thermal conductivity: 0.14 vs 0.22 W/mK\n• Chlorine resistance: CPVC resists; chlorine attacks PPR's polymer chain\n• Lower biofilm growth & thinner walls = higher flow\nFlowGuard® CPVC is the more durable, safer and more hygienic choice.",
      ar: "⚖️ **CPVC مقابل PPR — لماذا يتفوّق CPVC**\n• مقاومة الشدّ: CPVC **50 ميغاباسكال** مقابل 30 لـ PPR\n• مؤشر الأكسجين المحدّد: **60** مقابل 17 (أكثر أماناً في الحريق)\n• الاشتعال الوميضي: 480°م مقابل 340°م\n• التوصيل الحراري: 0.14 مقابل 0.22 واط/م.كلفن\n• مقاومة الكلور: CPVC يقاوم؛ بينما يهاجم الكلور سلسلة بوليمر PPR\n• نموّ أغشية حيوية أقل وجدران أرفع = تدفّق أعلى\nإنّ FlowGuard® CPVC هو الخيار الأكثر متانةً وأماناً ونظافة." },

    { k: ["copper","vs copper","نحاس","مقارنة نحاس","النحاس"],
      en: "⚖️ **CPVC vs Copper**\n• No corrosion or scale — Hazen-Williams C-factor stays ~150, while copper drops to 60–120 after years\n• Quieter: sound travels in the water, not the pipe (CPVC 1350 m/s vs copper 3600 m/s)\n• Lower head loss → smaller pump (10 HP vs 13 HP) and lower running cost\n• No condensation/sweating issues, no green corrosion in chlorinated water\nCPVC delivers copper-grade reliability at a much lower lifetime cost.",
      ar: "⚖️ **CPVC مقابل النحاس**\n• لا تآكل ولا ترسّبات — معامل C يبقى نحو 150، بينما يهبط النحاس إلى 60–120 بعد سنوات\n• أهدأ صوتاً: الصوت ينتقل في الماء لا في الأنبوب (CPVC 1350 م/ث مقابل 3600 للنحاس)\n• فقد ضغط أقل → مضخة أصغر (10 حصان مقابل 13) وتكلفة تشغيل أقل\n• لا مشاكل تكثّف أو تعرّق، ولا تآكل أخضر في المياه المكلورة\nيمنح CPVC موثوقية بمستوى النحاس بتكلفة عمرية أقل بكثير." },

    { k: ["fire","flame","burn","combustion","حريق","لهب","اشتعال","احتراق","نار"],
      en: "🔥 **Fire safety**\nFlowGuard® CPVC is rated **B-s1-d0** under EN 13501-1 — the best rating a non-metal can achieve:\n• Limiting Oxygen Index = 60 (needs 60% oxygen to burn)\n• Self-extinguishing, low flame spread, low smoke, **no flaming drips**\n• Flash-ignition temperature 480°C\nSafe for occupied multi-storey buildings.",
      ar: "🔥 **السلامة من الحريق**\nمصنّف FlowGuard® CPVC بدرجة **B-s1-d0** وفق EN 13501-1 — أفضل تصنيف يمكن أن تناله مادة غير معدنية:\n• مؤشر الأكسجين المحدّد = 60 (يحتاج 60% أكسجين ليشتعل)\n• ذاتي الإطفاء، انتشار لهب ودخان منخفض، **بلا قطرات مشتعلة**\n• درجة اشتعال وميضي 480°م\nآمن للمباني المأهولة متعددة الطوابق." },

    { k: ["chlorine","chlorine dioxide","disinfect","كلور","تعقيم","الكلور"],
      en: "🧪 **Chlorine & chlorine-dioxide resistance**\nThe large chlorine atoms in CPVC shield its carbon backbone from hypochlorous-acid attack, so chlorine simply chlorinates it further — no degradation. Polyolefins (PPR/PEX/PB) lack this protection and age prematurely. CPVC is the recommended pipe where chlorine dioxide disinfection is used.",
      ar: "🧪 **مقاومة الكلور وثاني أكسيد الكلور**\nذرّات الكلور الكبيرة في CPVC تحمي سلسلته الكربونية من هجوم حمض الهيبوكلوروز، فيكتفي الكلور بمزيد من الكلورة دون أي تدهور. أمّا البوليأوليفينات (PPR/PEX/PB) فتفتقر لهذه الحماية وتتقادم مبكراً. لذا يُوصى بـ CPVC حيث يُستخدم ثاني أكسيد الكلور للتعقيم." },

    { k: ["biofilm","bacteria","legionella","antimicrobial","hygiene","بكتيريا","أغشية","جراثيم","نظافة","ليجيونيلا"],
      en: "🦠 **Hygiene & biofilm resistance**\nIndependent KIWA & CRECEP studies (and Dr. Paul Sturman) show CPVC has the **lowest biofilm-formation potential** of common non-metallic pipes, and keeps Legionella growth low. Ideal for healthcare, hospitality and drinking-water systems.",
      ar: "🦠 **النظافة ومقاومة الأغشية الحيوية**\nتُظهر دراسات KIWA وCRECEP المستقلة (ود. بول ستورمان) أنّ CPVC يملك **أقل قابلية لتكوّن الأغشية الحيوية** بين الأنابيب غير المعدنية الشائعة، ويُبقي نموّ الليجيونيلا منخفضاً. مثالي للرعاية الصحية والضيافة وأنظمة مياه الشرب." },

    { k: ["uv","ultraviolet","sun","sunlight","outdoor","الشمس","اشعة","فوق البنفسجية","خارجي"],
      en: "☀️ **UV resistance**\nUV exposure only causes a surface discolouration on CPVC — the polymer chain is not broken down. Studies (incl. KFUPM, KSA) show up to 9 months of outdoor exposure had limited effect on strength. PPR, by contrast, loses hydrostatic strength under UV.",
      ar: "☀️ **مقاومة الأشعة فوق البنفسجية**\nلا يسبّب التعرّض للأشعة فوق البنفسجية سوى تغيّر لوني سطحي على CPVC — دون تكسير السلسلة البوليمرية. وتُظهر الدراسات (منها جامعة الملك فهد) أنّ تعرّضاً خارجياً حتى 9 أشهر كان أثره محدوداً على المتانة. أمّا PPR فيفقد متانته الهيدروستاتيكية تحت الأشعة." },

    { k: ["install","installation","joint","solvent","cement","weld","glue","تركيب","لصق","لحام","وصل","تجميع"],
      en: "🔧 **Installation — solvent cement welding**\nCPVC is joined by one-step solvent cementing (no electricity, cheap tools):\n1) Cut square  2) Deburr/bevel  3) Apply CPVC cement (per ASTM F493 / DIN EN ISO 15877)  4) Insert & rotate ¼–½ turn, hold ~10 s  5) Let set & cure.\nUse E-Z Weld 786 CPVC cement. The chemically-welded joint becomes the strongest part of the system.",
      ar: "🔧 **التركيب — اللحام باللاصق الكيميائي**\nيُوصَل CPVC باللصق الكيميائي بخطوة واحدة (بلا كهرباء وأدوات بسيطة):\n1) قطع مستقيم  2) إزالة النتوء والشطف  3) دهن لاصق CPVC (وفق ASTM F493 / DIN EN ISO 15877)  4) الإدخال مع لفّ ربع–نصف لفة والتثبيت ~10 ثوانٍ  5) الترك للتماسك والمعالجة.\nاستخدم لاصق E-Z Weld 786 CPVC. وتصبح الوصلة الملحومة كيميائياً أقوى جزء في النظام." },

    { k: ["cure","curing","set time","معالجة","وقت الجفاف","تجفيف","تماسك"],
      en: "⏱️ **Curing time (at 23°C, with FlowGuard cement)**\nFor 16–32 mm: ~12 h at 10 bar test, 36 h at 20 bar, 48 h at 30 bar. Larger sizes need longer. Below 15°C ambient, double the times. Always pressure-test after full cure.",
      ar: "⏱️ **زمن المعالجة (عند 23°م مع لاصق FlowGuard)**\nللمقاسات 16–32 مم: ~12 ساعة عند اختبار 10 بار، 36 ساعة عند 20 بار، 48 ساعة عند 30 بار. والمقاسات الأكبر تحتاج وقتاً أطول. وإذا قلّت حرارة الجو عن 15°م تُضاعَف المدد. اختبر الضغط دائماً بعد اكتمال المعالجة." },

    { k: ["warranty","lifespan","life","how long","last","durable","ضمان","عمر","يدوم","متانة","كم سنة"],
      en: "🛡️ **Lifespan & warranty**\nDesigned for a **50-year** service life with a manufacturer warranty (when genuine Al Waab FlowGuard pipe, fittings & CPVC cement are used together). Real-world CPVC installed in Baltimore in the 1960s showed no pipe-wall erosion after 23+ years.",
      ar: "🛡️ **العمر الافتراضي والضمان**\nمصمّم لعمر خدمة **50 عاماً** مع ضمان من المصنّع (عند استخدام أنبوب وتوصيلات ولاصق FlowGuard الأصلي من الوعب معاً). وقد أظهرت تركيبات CPVC حقيقية في بالتيمور منذ الستينيات عدم تآكل جدار الأنبوب بعد أكثر من 23 عاماً." },

    { k: ["material","made of","compound","lubrizol","raw","خامة","مصنوع","مادة الخام","ماده"],
      en: "🧬 **Material**\nGenuine CPVC (chlorinated PVC) compound supplied by **Lubrizol Corporation, USA** under the FlowGuard® brand, manufactured to DIN EN ISO 15877 & DIN 8079/8080. It is **lead-free** and NSF-certified safe for human consumption.",
      ar: "🧬 **المادة**\nخامة CPVC الأصلية (بولي فينيل كلوريد مكلور) من **شركة لوبريزول الأمريكية** تحت علامة FlowGuard®، مصنّعة وفق DIN EN ISO 15877 وDIN 8079/8080. وهي **خالية من الرصاص** ومعتمدة NSF كآمنة للاستهلاك البشري." },

    { k: ["standard","standards","astm","iso","din","norm","معيار","معايير","مواصفة قياسية"],
      en: "📋 **Standards**\nDIN EN ISO 15877 · DIN 8079/8080 · ASTM D2846 · ASTM F437/F439/F441 (fittings) · ASTM F493 (cement) · ASTM F656 (primer). Compliant with QCS, UPC, IPC and IBC plumbing codes.",
      ar: "📋 **المعايير**\nDIN EN ISO 15877 · DIN 8079/8080 · ASTM D2846 · ASTM F437/F439/F441 (للتوصيلات) · ASTM F493 (للّاصق) · ASTM F656 (للبرايمر). ومتوافق مع أكواد السباكة QCS وUPC وIPC وIBC." },

    { k: ["certif","approval","approved","nsf","wras","gsas","gord","kahramaa","شهادة","شهادات","اعتماد","معتمد"],
      en: "✅ **Certifications & approvals**\nNSF International (USA) · WRAS (UK) · GORD / GSAS · Dubai Municipality · Emirates Green Building Council · Kahramaa & Ashghal · Ministry of Interior. Full EPD/HPD and submittal packs available on request.",
      ar: "✅ **الشهادات والاعتمادات**\nNSF الدولية (أمريكا) · WRAS (بريطانيا) · GORD / GSAS · بلدية دبي · مجلس الإمارات للأبنية الخضراء · كهرماء وأشغال · وزارة الداخلية. وتتوفّر حزم EPD/HPD ومستندات التقديم عند الطلب." },

    { k: ["fitting","fittings","elbow","tee","coupler","valve","union","flange","توصيلة","توصيلات","كوع","تي","محبس","وصلة","فلنجة"],
      en: "🔩 **Fittings range**\nCouplers, 45°/90° elbows, reducer elbows, tees & reducer tees, long reducers, reducer bushes, end caps, flanges & blind flanges, unions, double-union ball valves, stop & concealed valves, mono-clips, plus brass threaded adaptors/elbows/tees — all 16–160 mm.",
      ar: "🔩 **نطاق التوصيلات**\nوصلات، أكواع 45°/90°، أكواع مخفّضة، تيهات وتيهات مخفّضة، مخفّضات طويلة، جلب تخفيض، طبّات نهاية، فلنجات وفلنجات عمياء، كلبسات، محابس كروية ثنائية الوصل، محابس إيقاف ومخفية، مشابك أحادية، إضافةً إلى مهايئات وأكواع وتيهات نحاسية لولبية — كلها 16–160 مم." },

    { k: ["price","cost","quote","buy","order","سعر","تكلفة","عرض سعر","شراء","طلب","اطلب"],
      en: "💬 **Pricing & quotes**\nSend us your sizes/quantities or BOQ and we'll return a spec-ready FlowGuard® CPVC package with full documentation.\n📞 +971 4 251 4228 · 📱 +971 54 769 6440 · ✉️ info@alwaab.ae\nOr use the Contact page to request a quote.",
      ar: "💬 **الأسعار وعروض الأسعار**\nأرسل لنا المقاسات/الكميات أو جدول الكميات وسنعيد إليك حزمة FlowGuard® CPVC جاهزة للمواصفات مع كامل الوثائق.\n📞 \u202A+971 4 251 4228\u202C · 📱 \u202A+971 54 769 6440\u202C · ✉️ info@alwaab.ae\nأو استخدم صفحة الاتصال لطلب عرض سعر." },

    { k: ["delivery","deliver","ship","lead time","توصيل","تسليم","شحن","مدة"],
      en: "🚚 **Delivery**\nWe deliver genuine FlowGuard® CPVC across all seven emirates, typically within **2–3 days**.",
      ar: "🚚 **التوصيل**\nنوصّل FlowGuard® CPVC الأصلي إلى الإمارات السبع جميعها، عادةً خلال **2–3 أيام**." },

    { k: ["location","address","where","office","showroom","عنوان","موقع","مكان","مكتب","وين","فين"],
      en: "📍 **Our location**\n206 Aboo Baker, Al Siddique Road, Mohamed Hasan Building, Deira, Dubai, UAE.\nWorking hours: Sun–Thu, 8:00 AM – 6:00 PM.",
      ar: "📍 **موقعنا**\n206 شارع أبو بكر الصديق، مبنى محمد حسن، ديرة، دبي، الإمارات.\nساعات العمل: الأحد–الخميس، 8:00 ص – 6:00 م." },

    { k: ["contact","phone","call","email","whatsapp","اتصال","هاتف","رقم","ايميل","بريد","تواصل","واتساب"],
      en: "📞 **Contact us**\nPhone: +971 4 251 4228\nMobile: +971 54 769 6440\nEmail: info@alwaab.ae\nDeira, Dubai, UAE.\n\n🌐 **Social Media:**\n• [Facebook](https://www.facebook.com/AlWaabBuildingContracting/)\n• [Instagram](https://www.instagram.com/alwaabbuilding)\n• [LinkedIn](https://www.linkedin.com/company/al-waab-building-contracting-l-l-c)",
      ar: "📞 **اتصل بنا**\nهاتف: \u202A+971 4 251 4228\u202C\nجوال: \u202A+971 54 769 6440\u202C\nبريد: info@alwaab.ae\nديرة، دبي، الإمارات.\n\n🌐 **وسائل التواصل الاجتماعي:**\n• [فيسبوك](https://www.facebook.com/AlWaabBuildingContracting/)\n• [انستقرام](https://www.instagram.com/alwaabbuilding)\n• [لينكد إن](https://www.linkedin.com/company/al-waab-building-contracting-l-l-c)" },

    { k: ["social","facebook","instagram","linkedin","links","فيس","فيسبوك","انستقرام","لينكد","سوشيال","روابط"],
      en: "🌐 **Social Media**\nFollow our latest projects here:\n• [Facebook](https://www.facebook.com/AlWaabBuildingContracting/)\n• [Instagram](https://www.instagram.com/alwaabbuilding)\n• [LinkedIn](https://www.linkedin.com/company/al-waab-building-contracting-l-l-c)",
      ar: "🌐 **منصات التواصل الاجتماعي**\nتابعوا أحدث مشاريعنا عبر الروابط التالية:\n• [فيسبوك](https://www.facebook.com/AlWaabBuildingContracting/)\n• [انستقرام](https://www.instagram.com/alwaabbuilding)\n• [لينكد إن](https://www.linkedin.com/company/al-waab-building-contracting-l-l-c)" },

    { k: ["expansion","thermal expansion","loop","تمدد","تمدّد حراري","حلقة"],
      en: "📐 **Thermal expansion**\nCPVC's expansion coefficient is low (~0.07). On long straight runs, accommodate movement with expansion loops/offsets — e.g. a 25 mm pipe over an 18 m run needs ~69 cm loop length (per the catalogue chart). Don't anchor tightly; use smooth straps.",
      ar: "📐 **التمدّد الحراري**\nمعامل تمدّد CPVC منخفض (~0.07). في الخطوط المستقيمة الطويلة، استوعب الحركة بحلقات/إزاحات تمدّد — مثلاً أنبوب 25 مم على مسار 18 م يحتاج طول حلقة ~69 سم (حسب جدول الكتالوج). لا تثبّت بإحكام؛ استخدم أحزمة ملساء." },

    { k: ["noise","quiet","sound","water hammer","صوت","ضجيج","هدوء","مطرقة"],
      en: "🔇 **Quieter than copper**\nThe modulus of elasticity of CPVC is far lower than copper, so water-hammer shock is much milder. Sound travels in the water rather than the pipe wall, making a CPVC system about as quiet as physically possible.",
      ar: "🔇 **أهدأ من النحاس**\nمعامل مرونة CPVC أقل بكثير من النحاس، لذا تكون صدمة المطرقة المائية أخف بكثير. وينتقل الصوت في الماء لا في جدار الأنبوب، مما يجعل نظام CPVC هادئاً قدر الإمكان." },

    { k: ["chiller","chilled","hvac","cold","تبريد","مبرد","ماء بارد"],
      en: "❄️ **Chilled / cold water**\nFlowGuard® CPVC is approved for chilled-water systems provided water stays above freezing. It has low thermal conductivity (0.14 W/mK), so insulation needs are modest and condensation is reduced versus metal.",
      ar: "❄️ **المياه المبرّدة / الباردة**\nمعتمد FlowGuard® CPVC لأنظمة المياه المبرّدة شرط بقاء الماء فوق درجة التجمّد. توصيله الحراري منخفض (0.14 واط/م.كلفن)، لذا حاجة العزل بسيطة والتكثّف أقل مقارنةً بالمعدن." },

    { k: ["recycle","recycling","environment","green","sustainab","eco","تدوير","بيئة","استدامة","اخضر","صديق"],
      en: "♻️ **Environment & sustainability**\nCPVC needs less energy to produce than HDPE/PP/PET, can be recycled as PVC/window profiles, and contributes to LEED & GSAS ratings. EPD/HPD declarations are available — our products are declared 'Green Products'.",
      ar: "♻️ **البيئة والاستدامة**\nيحتاج CPVC طاقة إنتاج أقل من HDPE/PP/PET، ويمكن تدويره كـ PVC/بروفايلات نوافذ، ويسهم في تصنيفات LEED وGSAS. وتتوفّر إقرارات EPD/HPD — ومنتجاتنا معلنة كـ«منتجات خضراء»." },

    { k: ["scale","corrosion","rust","تآكل","صدأ","ترسبات","ترسب"],
      en: "🛡️ **No corrosion, no scale build-up**\nWith CPVC there is no corrosion, so scale build-up is inhibited. Its Hazen-Williams C-factor stays around 150 even after decades, whereas copper/steel drop to 60–120 — meaning lower friction, lower head loss and steady flow over the system's life.",
      ar: "🛡️ **لا تآكل ولا ترسّبات**\nمع CPVC لا يوجد تآكل، لذا تُمنع الترسّبات. ويبقى معامل C نحو 150 حتى بعد عقود، بينما يهبط النحاس/الفولاذ إلى 60–120 — أي احتكاك أقل وفقد ضغط أقل وتدفّق ثابت طوال عمر النظام." }
  ];

  var GREET = { k: ["hi","hello","hey","salam","سلام","مرحبا","اهلا","أهلا","هاي","السلام"] };
  var THANKS = { k: ["thank","thanks","شكرا","شكراً","مشكور","يعطيك"] };

  function botReply(text) {
    var t = text.toLowerCase();
    var lang = /[؀-ۿ]/.test(text) ? "ar" : "en";
    function hit(arr) { var c = 0; arr.forEach(function (w) { if (t.indexOf(w.toLowerCase()) !== -1) c++; }); return c; }
    if (hit(GREET.k) && text.trim().split(/\s+/).length <= 3)
      return lang === "ar"
        ? "أهلاً بكم في FlowGuard® أنظمة السباكة! 👋 أنا مساعدكم الفني. يمكنني إفادتكم حول درجات الحرارة، الضغط، الأقطار المتوفرة، أو المقارنات الفنية مع PPR والنحاس."
        : "Welcome to FlowGuard® Plumbing Systems! 👋 I'm your technical assistant. Ask me about temperature, pressure, sizes, or comparisons vs PPR and copper.";
    if (hit(THANKS.k))
      return lang === "ar"
        ? "على الرحب والسعة، نحن في خدمتكم دائماً! 🙏 هل ترغبون في الحصول على عرض سعر فني لمشروعكم أو الوثائق الفنية؟ يمكنكم التواصل مباشرة عبر: \u202A+971 54 769 6440\u202C"
        : "You're welcome! 🙏 Want a quote or technical documents? Call +971 54 769 6440.";
    var best = null, bestScore = 0;
    KB.forEach(function (item) { var s = hit(item.k); if (s > bestScore) { bestScore = s; best = item; } });
    if (best && bestScore > 0) return best[lang];
    return lang === "ar"
      ? "شكراً لاستفساركم. يمكنني تزويدكم بتفاصيل دقيقة حول: الأداء الحراري 🌡️، تصنيفات الضغط 📊، الأقطار 📏، المقارنة الفنية ⚖️، السلامة من الحريق 🔥، مقاومة الكلور 🧪، طرق التركيب 🔧، الاعتمادات ✅، أو إجراءات طلب عرض السعر 💬.\nللتواصل المباشر: \u202A+971 54 769 6440\u202C · info@alwaab.ae"
      : "Good question! I can help with: temperature 🌡️, pressure 📊, sizes 📏, CPVC vs PPR/copper ⚖️, fire 🔥, chlorine 🧪, installation 🔧, certificates ✅, warranty 🛡️, or a quote 💬.\nFor details contact: \u202A+971 54 769 6440\u202C · info@alwaab.ae";
  }

  /* ---------------- Chatbot UI ---------------- */
  var bot = {};
  function buildBot() {
    var launch = document.createElement("button");
    launch.className = "cb-launch";
    launch.setAttribute("data-noi18n", "");
    launch.setAttribute("aria-label", "Open assistant");
    launch.innerHTML = '<span class="cb-ping">1</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>';
    document.body.appendChild(launch);

    var panel = document.createElement("div");
    panel.className = "chatbot";
    panel.setAttribute("data-noi18n", "");
    panel.innerHTML =
      '<div class="cb-head"><div class="cb-av"><img src="images/flowguard-plumbing-systems.png" alt="FlowGuard® Plumbing Systems"></div>' +
      '<div class="cb-ti"><b class="cb-name"></b><span class="cb-status"></span></div>' +
      '<button class="cb-x" aria-label="Close">&times;</button></div>' +
      '<div class="cb-body"></div>' +
      '<div class="cb-chips"></div>' +
      '<div class="cb-foot"><input type="text" class="cb-in"><button class="cb-send" aria-label="Send">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg></button></div>';
    document.body.appendChild(panel);

    bot.launch = launch;
    bot.panel = panel;
    bot.body = panel.querySelector(".cb-body");
    bot.chips = panel.querySelector(".cb-chips");
    bot.input = panel.querySelector(".cb-in");
    bot.name = panel.querySelector(".cb-name");
    bot.status = panel.querySelector(".cb-status");

    launch.addEventListener("click", openBot);
    panel.querySelector(".cb-x").addEventListener("click", function () { panel.classList.remove("open"); });
    panel.querySelector(".cb-send").addEventListener("click", send);
    bot.input.addEventListener("keydown", function (e) { if (e.key === "Enter") send(); });

    updateBotChrome();
  }

  function openBot() {
    bot.panel.classList.add("open");
    var ping = bot.launch.querySelector(".cb-ping");
    if (ping) ping.style.display = "none";
    if (!bot.body.children.length) {
      pushBot(curLang === "ar"
        ? "مرحباً بكم في منصة FlowGuard® CPVC الفنية! 👋\nيسعدني الرد على استفساراتكم الهندسية حول المواصفات، المقاسات، الاعتمادات، والمقارنات الفنية للمشاريع."
        : "Welcome to Al Waab Building Materials! 👋\nI'm the FlowGuard® CPVC smart assistant. Ask me anything about the products — temperature, pressure, sizes, comparisons, certificates and more.");
      renderChips();
    }
    setTimeout(function () { bot.input.focus(); }, 200);
  }

  function renderChips() {
    var chipsAr = ["درجة الحرارة 🌡️", "CPVC مقابل PPR ⚖️", "المقاسات 📏", "الشهادات ✅", "عرض سعر 💬"];
    var chipsEn = ["Temperature 🌡️", "CPVC vs PPR ⚖️", "Sizes 📏", "Certificates ✅", "Get a quote 💬"];
    var list = curLang === "ar" ? chipsAr : chipsEn;
    bot.chips.innerHTML = "";
    list.forEach(function (label) {
      var c = document.createElement("button");
      c.className = "cb-chip";
      c.textContent = label;
      c.addEventListener("click", function () { handle(label); });
      bot.chips.appendChild(c);
    });
  }

  function pushBot(text) {
    var m = document.createElement("div");
    m.className = "cb-msg bot";
    m.innerHTML = fmt(text);
    bot.body.appendChild(m);
    bot.body.scrollTop = bot.body.scrollHeight;
  }
  function pushUser(text) {
    var m = document.createElement("div");
    m.className = "cb-msg user";
    m.textContent = text;
    bot.body.appendChild(m);
    bot.body.scrollTop = bot.body.scrollHeight;
  }
  function fmt(s) {
    if (!s) return "";
    var res = s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    res = res.replace(/\*\*(.+?)\*\*/g, "<b>$1</b>");
    res = res.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function(_, text, url) {
      return '<a href="' + url.trim() + '" target="_blank" rel="noopener noreferrer">' + text + '</a>';
    });
    return res.replace(/\n/g, "<br>");
  }
  function typing() {
    var t = document.createElement("div");
    t.className = "cb-typing";
    t.innerHTML = "<span></span><span></span><span></span>";
    bot.body.appendChild(t);
    bot.body.scrollTop = bot.body.scrollHeight;
    return t;
  }
  function handle(text) {
    pushUser(text);
    var t = typing();
    setTimeout(function () { t.remove(); pushBot(botReply(text)); }, 550);
  }
  function send() {
    var v = bot.input.value.trim();
    if (!v) return;
    bot.input.value = "";
    handle(v);
  }
  function updateBotChrome() {
    if (!bot.name) return;
    bot.name.textContent = curLang === "ar" ? "FlowGuard® · أنظمة السباكة" : "FlowGuard® Plumbing Systems";
    bot.status.textContent = curLang === "ar" ? "متصل حالياً · استجابة فورية" : "Online · Immediate response";
    bot.input.setAttribute("placeholder", curLang === "ar"
      ? "اكتب سؤالك هنا..." : "Type your question here...");
    if (bot.chips && bot.chips.children.length) renderChips();
  }

  /* =================================================================
     PROFESSIONAL ANIMATIONS - Mouse tracking & Water flow effects
  ================================================================= */
  
  // Mouse tracking for water ripple effect on cards
  var cards = document.querySelectorAll('.card, .vmv-card, .why-card, .cert, .dist');
  cards.forEach(function(card) {
    card.addEventListener('mousemove', function(e) {
      var rect = card.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;
      card.style.setProperty('--mouse-x', x + 'px');
      card.style.setProperty('--mouse-y', y + 'px');
    });
  });

  /* =================================================================
     QUOTE CART SYSTEM - Products page shopping cart functionality
     Enhanced with size selection and quantity input
     Now supports Excel-uploaded products
  ================================================================= */
  var quoteCart = [];
  var quoteCartOpen = false;
  var quoteModalOpen = false;
  var excelProducts = []; // Products loaded from Excel

  function escapeAttr(s) {
    return String(s).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;");
  }

  function buildProductsList() {
    var productsList = "";
    quoteCart.forEach(function(product) {
      productsList += "\n" + product.name + ":\n";
      var sizes = Object.keys(product.selectedSizes || {});
      if (sizes.length === 0) {
        productsList += "  • (quantities to be confirmed)\n";
      } else {
        sizes.forEach(function(size) {
          productsList += "  • " + size + ": " + product.selectedSizes[size] + "\n";
        });
      }
    });
    return productsList;
  }

  function getQuoteFormFields(form) {
    var isModal = form && form.id === "modalQuoteForm";
    var prefix = isModal ? "mq" : "pq";
    function val(id) {
      var el = document.getElementById(id);
      return el ? el.value : "";
    }
    return {
      isModal: isModal,
      prefix: prefix,
      name: val(prefix + "-name"),
      company: val(prefix + "-company"),
      email: val(prefix + "-email"),
      phone: val(prefix + "-phone"),
      project: val(prefix + "-project"),
      msg: val(prefix + "-msg")
    };
  }

  var PRODUCT_SIZES = {};

  function buildProductSizesFromCatalog() {
    if (!window.PRODUCTS_CATALOG) return;
    window.PRODUCTS_CATALOG.forEach(function(p) {
      PRODUCT_SIZES[p.id] = { name: p.name, image: p.image, sizes: p.sizes.slice() };
    });
  }

  function renderSizeChips(sizes) {
    var preview = sizes.slice(0, 4);
    var html = '<div class="size-chips">';
    preview.forEach(function(s) {
      html += '<span class="size-chip">' + s + '</span>';
    });
    if (sizes.length > 4) {
      html += '<span class="size-chip">+' + (sizes.length - 4) + ' sizes</span>';
    }
    html += '</div>';
    return html;
  }

  function renderOfficialCatalog() {
    var container = document.getElementById("productCards");
    if (!container || !window.PRODUCTS_CATALOG) return;

    container.innerHTML = "";
    var lastSection = "";

    window.PRODUCTS_CATALOG.forEach(function(p) {
      if (p.section !== lastSection) {
        lastSection = p.section;
        var head = document.createElement("div");
        head.className = "catalog-section-head reveal";
        head.setAttribute("data-section", p.section);
        head.innerHTML = '<span class="eyebrow">' + p.section + '</span>';
        container.appendChild(head);
      }

      var card = document.createElement("article");
      card.className = "card reveal";
      card.setAttribute("data-id", p.id);
      card.setAttribute("data-name", p.name);
      card.setAttribute("data-image", p.image);
      card.setAttribute("data-category", p.category);
      card.setAttribute("data-section", p.section);

      card.innerHTML =
        '<span class="tag">' + p.tag + '</span>' +
        '<div class="card-media"><img src="' + p.image + '" alt="' + p.name + '" loading="lazy"></div>' +
        '<div class="card-body">' +
          '<h3>' + p.name + '</h3>' +
          '<p>' + p.description + '</p>' +
          renderSizeChips(p.sizes) +
          '<button class="add-quote-btn" onclick="addToQuote(this)" data-id="' + p.id + '" data-name="' + p.name + '" data-image="' + p.image + '">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>' +
            'Add to Quote' +
          '</button>' +
        '</div>';

      container.appendChild(card);
    });

    buildProductSizesFromCatalog();
    initProductImages();
    applyProductFilters();
    if (typeof walkTranslate === "function") walkTranslate(curLang);
  }

  // Add product to quote cart
  window.addToQuote = function(btn) {
    var id = btn.getAttribute('data-id');
    var name = btn.getAttribute('data-name');
    var image = btn.getAttribute('data-image');
    
    // Check if already in cart
    if (quoteCart.find(function(item) { return item.id === id; })) {
      // Remove from cart
      quoteCart = quoteCart.filter(function(item) { return item.id !== id; });
      btn.classList.remove('added');
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Add to Quote';
    } else {
      // Add to cart with default size and quantity
      var productInfo = PRODUCT_SIZES[id] || { name: name, image: image, sizes: [] };
      quoteCart.push({ 
        id: id, 
        name: productInfo.name, 
        image: productInfo.image,
        sizes: productInfo.sizes,
        selectedSizes: {}
      });
      btn.classList.add('added');
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg> Added';
    }
    
    updateQuoteCartUI();
  };

  // Remove product from quote cart
  window.removeFromQuote = function(id) {
    quoteCart = quoteCart.filter(function(item) { return item.id !== id; });
    // Update button state
    var btn = document.querySelector('.add-quote-btn[data-id="' + id + '"]');
    if (btn) {
      btn.classList.remove('added');
      btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Add to Quote';
    }
    updateQuoteCartUI();
  };

  // Update quantity for a size
  window.updateSizeQuantity = function(productId, size, qty) {
    var product = quoteCart.find(function(item) { return item.id === productId; });
    if (product) {
      if (qty <= 0) {
        delete product.selectedSizes[size];
      } else {
        product.selectedSizes[size] = qty;
      }
      // Remove empty sizes
      Object.keys(product.selectedSizes).forEach(function(s) {
        if (product.selectedSizes[s] <= 0) delete product.selectedSizes[s];
      });
      saveQuoteCart();
      updateQuoteCartUI();
      updateModalProduct(productId);
    }
  };

  // Get total items count
  function getTotalItems() {
    var total = 0;
    quoteCart.forEach(function(product) {
      Object.values(product.selectedSizes).forEach(function(qty) {
        total += qty;
      });
    });
    return total;
  }

  // Update quote cart UI
  function updateQuoteCartUI() {
    var cartBtn = document.getElementById('quoteCartBtn');
    var cartCount = document.getElementById('cartCount');
    var topBar = document.getElementById('quoteTopBar');
    var topCount = document.getElementById('quoteTopCount');
    var topHint = document.getElementById('quoteTopHint');
    var topProceed = document.getElementById('quoteTopProceed');
    var qcItems = document.getElementById('qcItems');
    var qcFooter = document.getElementById('qcFooter');
    var qcTotalCount = document.getElementById('qcTotalCount');
    var quoteFormSection = document.getElementById('quoteFormSection');
    
    var totalItems = getTotalItems();
    var count = quoteCart.length;

    // Legacy floating button (other pages)
    if (cartBtn) {
      cartBtn.style.display = count > 0 ? 'flex' : 'none';
      if (cartCount) cartCount.textContent = count;
    }

    // Top quote bar (products page)
    if (topBar) {
      topBar.classList.toggle('has-items', count > 0);
      if (topCount) {
        topCount.textContent = count;
        topCount.setAttribute('data-count', count);
      }
      if (topHint) {
        topHint.textContent = count > 0
          ? (curLang === 'ar' ? count + ' منتج/منتجات في طلبك' : count + ' product(s) in your quote')
          : (curLang === 'ar' ? 'أضف المنتجات أدناه لبناء طلب عرض السعر' : 'Add products below to build your quote');
      }
      if (topProceed) topProceed.style.display = count > 0 ? 'inline-flex' : 'none';
    }
    
    // Update sidebar items
    if (qcItems) {
      if (quoteCart.length === 0) {
        qcItems.innerHTML = '<div class="qc-empty" id="qcEmpty">' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 01-8 0"/></svg>' +
          '<p>No products selected</p>' +
          '<span style="font-size:13px">Add products to request a quote</span>' +
          '</div>';
        if (qcFooter) qcFooter.style.display = 'none';
      } else {
        var itemsHtml = '';
        quoteCart.forEach(function(item) {
          var sizeCount = Object.keys(item.selectedSizes).length;
          itemsHtml += '<div class="qc-item">' +
            '<img src="' + item.image + '" alt="' + item.name + '">' +
            '<div class="qc-item-info"><h4>' + item.name + '</h4><p>' + sizeCount + ' size(s) selected</p></div>' +
            '<button class="qc-item-remove" onclick="removeFromQuote(\'' + item.id + '\')">&times;</button>' +
            '</div>';
        });
        qcItems.innerHTML = itemsHtml;
        if (qcFooter) {
          qcFooter.style.display = 'block';
          if (qcTotalCount) qcTotalCount.textContent = quoteCart.length;
        }
      }
    }
    
    // Update quote form section
    if (quoteFormSection) {
      quoteFormSection.style.display = quoteCart.length > 0 ? 'block' : 'none';
      updateSelectedProductsList();
    }
    
    saveQuoteCart();
  }

  // Update selected products list in form
  function updateSelectedProductsList() {
    var list = document.getElementById('selectedProductsList');
    if (!list) return;
    
    if (quoteCart.length === 0) {
      list.innerHTML = '<span style="color:var(--muted);font-size:14px">No products selected</span>';
      return;
    }
    
    var html = '';
    quoteCart.forEach(function(product) {
      var sizesStr = Object.keys(product.selectedSizes).map(function(s) {
        return s + ': ' + product.selectedSizes[s];
      }).join(', ');
      html += '<span class="chip" style="background:var(--blue);color:#fff;font-weight:600;font-size:12px">' + 
        product.name + (sizesStr ? ' (' + sizesStr + ')' : '') + '</span>';
    });
    list.innerHTML = html;
  }

  // Toggle quote cart sidebar
  window.toggleQuoteCart = function() {
    var sidebar = document.getElementById('quoteCartSidebar');
    if (!sidebar) return;

    if (quoteModalOpen) closeQuoteModal();

    quoteCartOpen = !quoteCartOpen;
    sidebar.classList.toggle('open', quoteCartOpen);
  };

  // Open full quote modal with all products
  window.openQuoteModal = function() {
    if (quoteCartOpen) {
      quoteCartOpen = false;
      var sidebar = document.getElementById('quoteCartSidebar');
      if (sidebar) sidebar.classList.remove('open');
    }

    var modal = document.getElementById('quoteModal');
    if (!modal) {
      createQuoteModal();
      modal = document.getElementById('quoteModal');
    }

    renderQuoteModal();
    modal.classList.add('open');
    quoteModalOpen = true;
    document.body.style.overflow = 'hidden';
  };

  // Sidebar → modal flow (size/qty selection + submit)
  window.proceedToQuote = function() {
    openQuoteModal();
  };

  // Scroll to inline quote form on products page
  window.scrollToQuoteForm = function() {
    if (quoteCartOpen) toggleQuoteCart();

    var section = document.getElementById('quoteFormSection');
    if (!section) {
      openQuoteModal();
      return;
    }

    section.style.display = 'block';
    updateSelectedProductsList();
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  // Close quote modal
  window.closeQuoteModal = function() {
    var modal = document.getElementById('quoteModal');
    if (modal) modal.classList.remove('open');
    quoteModalOpen = false;
    document.body.style.overflow = '';
  };

  // Create quote modal HTML
  function createQuoteModal() {
    var modal = document.createElement('div');
    modal.id = 'quoteModal';
    modal.className = 'quote-modal';
    modal.innerHTML = 
      '<div class="quote-modal-overlay" onclick="closeQuoteModal()"></div>' +
      '<div class="quote-modal-content">' +
        '<div class="qm-header">' +
          '<h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 01-8 0"/></svg> Request a Quote</h2>' +
          '<button class="qm-close" onclick="closeQuoteModal()">&times;</button>' +
        '</div>' +
        '<div class="qm-body">' +
          '<div class="qm-products" id="qmProducts"></div>' +
          '<div class="qm-form" id="qmFormSection" style="display:none">' +
            '<h3>Your Details</h3>' +
            '<form id="modalQuoteForm">' +
              '<div class="form-row">' +
                '<div class="field"><label>Full Name</label><input type="text" id="mq-name" placeholder="Your name" required></div>' +
                '<div class="field"><label>Company</label><input type="text" id="mq-company" placeholder="Company name"></div>' +
              '</div>' +
              '<div class="form-row">' +
                '<div class="field"><label>Email</label><input type="email" id="mq-email" placeholder="you@email.com" required></div>' +
                '<div class="field"><label>Phone</label><input type="tel" id="mq-phone" placeholder="+971 ..."></div>' +
              '</div>' +
              '<div class="field"><label>Project Name / Location</label><input type="text" id="mq-project" placeholder="e.g., Dubai Marina Tower"></div>' +
              '<div class="field"><label>Project Details / Quantities / BOQ</label><textarea id="mq-msg" placeholder="Tell us about your project requirements..."></textarea></div>' +
              '<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Submit Quote Request</button>' +
            '</form>' +
            '<p class="form-note" id="mq-success" style="display:none;margin-top:16px;color:#0a8a3a;font-weight:600;background:#e9f9ef;padding:16px;border-radius:12px;text-align:center"></p>' +
          '</div>' +
          '<div class="qm-empty" id="qmEmpty">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:64px;height:64px;opacity:.3;margin-bottom:16px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 01-8 0"/></svg>' +
            '<h3>No products selected</h3>' +
            '<p>Browse our products and add items to your quote request.</p>' +
            '<a href="products.html" class="btn btn-primary">Browse Products</a>' +
          '</div>' +
        '</div>' +
        '<div class="qm-footer" id="qmFooter" style="display:none">' +
          '<div class="qm-total"><b id="qmTotalItems">0</b> total items</div>' +
          '<button class="btn btn-primary" onclick="showQuoteForm()" id="qmProceedBtn">Proceed to Request</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(modal);

    var modalForm = modal.querySelector('#modalQuoteForm');
    if (modalForm) modalForm.addEventListener('submit', handleQuoteFormSubmit);

    // Add modal styles
    var style = document.createElement('style');
    style.textContent = 
      '.quote-modal{position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.3s var(--ease)}' +
      '.quote-modal.open{opacity:1;pointer-events:auto}' +
      '.quote-modal-overlay{position:absolute;inset:0;background:rgba(6,22,64,.7);backdrop-filter:blur(4px)}' +
      '.quote-modal-content{position:relative;background:#fff;border-radius:20px;width:90%;max-width:800px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;transform:translateY(20px);transition:.3s var(--ease)}' +
      '.quote-modal.open .quote-modal-content{transform:none}' +
      '.qm-header{background:linear-gradient(135deg,var(--navy),var(--blue-700));color:#fff;padding:20px 24px;display:flex;align-items:center;gap:12px}' +
      '.qm-header h2{font-size:20px;color:#fff;margin:0;display:flex;align-items:center;gap:8px}' +
      '.qm-close{margin-inline-start:auto;background:rgba(255,255,255,.12);border:0;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:22px;display:grid;place-items:center}' +
      '.qm-close:hover{background:rgba(255,255,255,.25)}' +
      '.qm-body{flex:1;overflow-y:auto;padding:24px;background:var(--bg-soft)}' +
      '.qm-products{display:grid;gap:16px}' +
      '.qm-product{background:#fff;border-radius:16px;padding:20px;border:1px solid var(--line)}' +
      '.qm-product-header{display:flex;gap:16px;align-items:center;margin-bottom:16px}' +
      '.qm-product-header img{width:80px;height:80px;object-fit:contain;background:#f4f7fc;border-radius:12px;padding:6px}' +
      '.qm-product-header h3{font-size:18px;margin:0;color:var(--navy)}' +
      '.qm-product-header .qm-remove{margin-inline-start:auto;background:none;border:0;color:var(--muted);cursor:pointer;font-size:20px;padding:8px}' +
      '.qm-product-header .qm-remove:hover{color:var(--red)}' +
      '.qm-sizes{display:grid;gap:10px}' +
      '.qm-size-row{display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg-soft);border-radius:10px}' +
      '.qm-size-row label{flex:1;font-weight:600;font-size:14px;color:var(--ink)}' +
      '.qm-size-row input{width:80px;padding:8px 12px;border:1.5px solid var(--line);border-radius:8px;font-size:14px;text-align:center}' +
      '.qm-size-row input:focus{outline:0;border-color:var(--blue);background:#fff}' +
      '.qm-form{margin-top:24px;background:#fff;border-radius:16px;padding:24px}' +
      '.qm-form h3{font-size:18px;margin-bottom:20px;color:var(--navy)}' +
      '.qm-empty{text-align:center;padding:60px 24px}' +
      '.qm-empty h3{font-size:20px;margin-bottom:8px;color:var(--navy)}' +
      '.qm-empty p{color:var(--muted);margin-bottom:24px}' +
      '.qm-footer{padding:16px 24px;background:#fff;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:16px}' +
      '.qm-total{font-size:15px;color:var(--muted)}' +
      '.qm-total b{color:var(--navy);font-size:18px}' +
      '@media(max-width:760px){.quote-modal-content{width:95%;max-height:95vh;border-radius:16px}.qm-product-header{flex-wrap:wrap}.qm-size-row{flex-wrap:wrap}.qm-footer{flex-direction:column}}';
    document.head.appendChild(style);
  }

  // Render quote modal content
  function renderQuoteModal() {
    var productsContainer = document.getElementById('qmProducts');
    var emptyState = document.getElementById('qmEmpty');
    var footer = document.getElementById('qmFooter');
    var totalItems = document.getElementById('qmTotalItems');
    
    if (quoteCart.length === 0) {
      productsContainer.innerHTML = '';
      emptyState.style.display = 'block';
      footer.style.display = 'none';
      return;
    }
    
    emptyState.style.display = 'none';
    footer.style.display = 'flex';
    totalItems.textContent = getTotalItems();
    
    var html = '';
    quoteCart.forEach(function(product) {
      var sizesHtml = '';
      if (!product.sizes || product.sizes.length === 0) {
        sizesHtml = '<p style="font-size:13px;color:var(--muted);margin:0">Specify quantities in the project details below.</p>';
      } else {
        product.sizes.forEach(function(size) {
          var qty = product.selectedSizes[size] || 0;
          sizesHtml += '<div class="qm-size-row">' +
            '<label>' + size + '</label>' +
            '<input type="number" min="0" value="' + qty + '" ' +
            'data-product-id="' + escapeAttr(product.id) + '" data-size="' + escapeAttr(size) + '"' +
            ' placeholder="0">' +
            '</div>';
        });
      }
      
      html += '<div class="qm-product">' +
        '<div class="qm-product-header">' +
          '<img src="' + product.image + '" alt="' + product.name + '">' +
          '<h3>' + product.name + '</h3>' +
          '<button class="qm-remove" onclick="removeFromQuote(\'' + product.id + '\')">&times;</button>' +
        '</div>' +
        '<div class="qm-sizes">' + sizesHtml + '</div>' +
        '</div>';
    });
    
    productsContainer.innerHTML = html;
    bindModalSizeInputs();
  }

  function bindModalSizeInputs() {
    document.querySelectorAll('.qm-size-row input[type="number"]').forEach(function(input) {
      input.addEventListener('input', function() {
        updateSizeQuantity(
          this.getAttribute('data-product-id'),
          this.getAttribute('data-size'),
          parseInt(this.value, 10) || 0
        );
      });
    });
  }

  // Update a single product in modal
  function updateModalProduct(productId) {
    var product = quoteCart.find(function(item) { return item.id === productId; });
    if (!product) return;

    var totalItems = document.getElementById('qmTotalItems');
    if (totalItems) totalItems.textContent = getTotalItems();

    document.querySelectorAll('.qm-size-row input[data-product-id="' + productId + '"]').forEach(function(input) {
      var size = input.getAttribute('data-size');
      input.value = product.selectedSizes[size] || 0;
    });
  }

  // Show quote form in modal
  window.showQuoteForm = function() {
    var formSection = document.getElementById('qmFormSection');
    var productsContainer = document.getElementById('qmProducts');
    var footer = document.getElementById('qmFooter');
    
    productsContainer.style.display = 'none';
    footer.style.display = 'none';
    formSection.style.display = 'block';
    formSection.scrollIntoView({ behavior: 'smooth' });
  };

  // Save cart to localStorage
  function saveQuoteCart() {
    localStorage.setItem('quoteCart', JSON.stringify(quoteCart));
  }

  // Handle quote form submission (modal or inline products page form)
  function handleQuoteFormSubmit(e) {
    e.preventDefault();

    if (quoteCart.length === 0) {
      alert(curLang === 'ar' ? 'الرجاء اختيار منتج واحد على الأقل' : 'Please select at least one product');
      return;
    }

    var fields = getQuoteFormFields(e.target);
    var totalQty = getTotalItems();

    if (fields.isModal && totalQty === 0) {
      var hasSizeOptions = quoteCart.some(function(p) { return p.sizes && p.sizes.length > 0; });
      if (hasSizeOptions) {
        alert(curLang === 'ar' ? 'الرجاء تحديد الكميات للمنتجات' : 'Please specify quantities for the products');
        return;
      }
    }

    if (!window.AlwaabErp) {
      alert(curLang === 'ar'
        ? 'إعدادات النظام غير مكتملة. تواصل معنا على info@alwaab.ae'
        : 'System configuration missing. Please contact info@alwaab.ae');
      return;
    }

    var submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = curLang === 'ar' ? 'جاري الإرسال...' : 'Sending...';
    }

    var items = quoteCart.map(function(product) {
      return {
        name: product.name,
        sku: product.id || null,
        sizes: product.selectedSizes || {},
      };
    });

    var messageParts = [];
    if (fields.msg) messageParts.push(fields.msg);
    messageParts.push('Total items: ' + (totalQty || quoteCart.length));

    window.AlwaabErp.submitQuoteRequest({
      name: fields.name,
      company: fields.company,
      email: fields.email,
      phone: fields.phone,
      project: fields.project,
      message: messageParts.join('\n\n'),
      page: fields.isModal ? 'products-modal' : 'products',
      items: items,
    }).then(function() {
      var successMsg = document.getElementById(fields.prefix + '-success');
      if (successMsg) {
        successMsg.style.display = 'block';
        successMsg.textContent = curLang === 'ar'
          ? 'تم إرسال طلب عرض السعر بنجاح! سنتواصل معك قريباً.'
          : 'Quote request sent successfully! We will contact you soon.';
      }

      if (e.target && e.target.reset) e.target.reset();

      quoteCart = [];
      localStorage.removeItem('quoteCart');
      updateQuoteCartUI();

      if (fields.isModal) {
        setTimeout(function() { closeQuoteModal(); }, 3000);
      } else {
        setTimeout(function() {
          var section = document.getElementById('quoteFormSection');
          if (section) section.style.display = 'none';
          if (successMsg) successMsg.style.display = 'none';
        }, 5000);
      }
    }).catch(function(err) {
      alert(curLang === 'ar'
        ? 'تعذر إرسال الطلب. راسلنا على info@alwaab.ae'
        : 'Could not send request. Please email info@alwaab.ae');
      console.error(err);
    }).finally(function() {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = (curLang === 'ar' ? 'إرسال طلب عرض السعر' : 'Submit Quote Request') +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
      }
    });
  }

  // Load cart from localStorage on page load
  function loadQuoteCart() {
    var saved = localStorage.getItem('quoteCart');
    if (saved) {
      try {
        quoteCart = JSON.parse(saved);
        updateQuoteCartUI();
        
        // Update button states
        quoteCart.forEach(function(item) {
          var btn = document.querySelector('.add-quote-btn[data-id="' + item.id + '"]');
          if (btn) {
            btn.classList.add('added');
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg> Added';
          }
        });
      } catch(e) {
        quoteCart = [];
      }
    }
  }

  // Load Excel products and render them on products page
  function loadExcelProducts() {
    var saved = localStorage.getItem('excelProducts');
    if (!saved) return;
    
    try {
      excelProducts = JSON.parse(saved);
      if (!excelProducts || excelProducts.length === 0) return;
      
      updateQuoteCartUI();
      
      var container = document.getElementById('productCards');
      if (!container) return;
      
      // Add section title for Excel products
      var sectionTitle = document.createElement('div');
      sectionTitle.className = 'sh text-center reveal';
      sectionTitle.style.marginTop = '40px';
      sectionTitle.style.paddingTop = '40px';
      sectionTitle.style.borderTop = '2px solid var(--line)';
      sectionTitle.innerHTML = '<span class="eyebrow" style="justify-content:center">Custom Catalog</span><h2>Uploaded Products</h2><p>Products from your uploaded Excel catalog.</p>';
      container.parentNode.insertBefore(sectionTitle, container.nextSibling);
      
      // Render each Excel product
      excelProducts.forEach(function(product) {
        // Check if product already exists in PRODUCT_SIZES
        if (!PRODUCT_SIZES[product.id]) {
          PRODUCT_SIZES[product.id] = {
            name: product.name,
            image: product.image || 'images/flowguard.jpg',
            sizes: product.sizes || []
          };
        }
        
        var card = document.createElement('article');
        card.className = 'card reveal';
        card.setAttribute('data-id', product.id);
        card.setAttribute('data-name', product.name);
        card.setAttribute('data-image', product.image || 'images/flowguard.jpg');
        card.setAttribute('data-category', 'custom');
        
        var imageSrc = product.image || 'images/flowguard.jpg';
        var description = product.description || 'Custom product from uploaded catalog.';
        
        card.innerHTML = 
          '<span class="tag">Custom</span>' +
          '<div class="card-media"><img src="' + imageSrc + '" alt="' + product.name + '" onerror="this.src=\'images/flowguard.jpg\'"></div>' +
          '<div class="card-body">' +
            '<h3>' + product.name + '</h3>' +
            '<p>' + description + (product.category ? ' Category: ' + product.category : '') + '</p>' +
            '<button class="add-quote-btn" onclick="addToQuote(this)" data-id="' + product.id + '" data-name="' + product.name + '" data-image="' + imageSrc + '">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>' +
              'Add to Quote' +
            '</button>' +
          '</div>';
        
        container.appendChild(card);
      });
      
      // Re-trigger animations for new cards
      setTimeout(function() {
        var newCards = container.querySelectorAll('.card.reveal');
        newCards.forEach(function(card) {
          card.classList.add('in');
        });
        applyProductFilters();
      }, 100);
      
    } catch(e) {
      console.error('Error loading Excel products:', e);
    }
  }

  /* ---------------- Product filters (products page) ---------------- */
  var activeProductFilter = "all";
  var productSearchQuery = "";

  function applyProductFilters() {
    var cards = document.querySelectorAll("#productCards .card");
    var query = productSearchQuery.toLowerCase().trim();
    var visible = 0;
    var visibleSections = {};

    cards.forEach(function(card) {
      var category = card.getAttribute("data-category") || "custom";
      var h3 = card.querySelector("h3");
      var descEl = card.querySelector(".card-body p");
      var name = (card.getAttribute("data-name") || (h3 ? h3.textContent : "") || "").toLowerCase();
      var desc = (descEl ? descEl.textContent : "").toLowerCase();
      var matchFilter = activeProductFilter === "all" || category === activeProductFilter;
      var matchSearch = !query || name.indexOf(query) !== -1 || desc.indexOf(query) !== -1;
      var show = matchFilter && matchSearch;

      card.classList.toggle("product-hidden", !show);
      if (show) {
        visible++;
        visibleSections[card.getAttribute("data-section")] = true;
      }
    });

    document.querySelectorAll("#productCards .catalog-section-head").forEach(function(head) {
      var sec = head.getAttribute("data-section");
      head.classList.toggle("section-hidden", !visibleSections[sec]);
    });

    var countEl = document.getElementById("productCount");
    if (countEl) {
      countEl.textContent = curLang === "ar"
        ? visible + " منتج معروض"
        : visible + " product" + (visible === 1 ? "" : "s") + " shown";
    }
  }

  function initProductImages() {
    var fallback = "images/flowguard.jpg";
    document.querySelectorAll("#productCards .card-media img").forEach(function(img) {
      if (!img.getAttribute("loading")) img.setAttribute("loading", "lazy");
      img.addEventListener("error", function onImgErr() {
        if (img.src.indexOf("flowguard.jpg") === -1) {
          img.src = fallback;
        }
      });
    });
    document.querySelectorAll("#productCards .card").forEach(function(card) {
      var img = card.querySelector(".card-media img");
      if (!img) return;
      var src = img.getAttribute("src") || "";
      card.setAttribute("data-image", src);
      var btn = card.querySelector(".add-quote-btn");
      if (btn) btn.setAttribute("data-image", src);
    });
  }

  function initProductFilters() {
    var container = document.getElementById("productFilters");
    if (!container) return;

    container.querySelectorAll(".pf-tab").forEach(function(tab) {
      tab.addEventListener("click", function() {
        activeProductFilter = this.getAttribute("data-filter") || "all";
        container.querySelectorAll(".pf-tab").forEach(function(t) {
          var on = t === tab;
          t.classList.toggle("active", on);
          t.setAttribute("aria-selected", on ? "true" : "false");
        });
        applyProductFilters();
      });
    });

    var search = document.getElementById("productSearch");
    if (search) {
      search.addEventListener("input", function() {
        productSearchQuery = this.value;
        applyProductFilters();
      });
    }

    applyProductFilters();
  }

  /* ---------------- Init ---------------- */
  function init() {
    buildLangBtn();
    buildBot();
    renderCatalog();
    setLang(curLang); // applies saved language + RTL
    loadQuoteCart(); // Load quote cart from localStorage
    if (document.getElementById("productCards") && window.PRODUCTS_CATALOG) {
      renderOfficialCatalog();
    }
    loadExcelProducts();
    initProductFilters();
    if (document.getElementById("quoteTopBar")) {
      removeLegacyQuoteBadge();
    }

    // Attach form submit handler if on products page
    var quoteForm = document.getElementById('productsQuoteForm');
    if (quoteForm) {
      quoteForm.addEventListener('submit', handleQuoteFormSubmit);
    }
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
