/* Al Waab — FlowGuard® CPVC Schedule of Items (official catalog) */
(function () {
  "use strict";

  function pnSizes(mmList, pnMap) {
    return mmList.map(function (mm) {
      var pn = typeof pnMap === "object" ? pnMap[mm] : pnMap;
      return mm + "mm PN" + pn;
    });
  }

  window.PRODUCTS_CATALOG = [
    {
      id: "pipe-pn16",
      category: "pipes",
      section: "CPVC FlowGuard® Pipes",
      tag: "PN16 · S6.3",
      name: "CPVC Pipe S6.3 — PN16",
      description: "FlowGuard® CPVC pipes per DIN 8079/8080 & DIN EN ISO 15877-2. Rated PN16 for hot & cold potable water.",
      image: "protect/cpvcPipes%20(1).jpg",
      sizes: ["Ø20mm", "Ø25mm", "Ø32mm", "Ø40mm", "Ø50mm", "Ø63mm", "Ø75mm", "Ø90mm", "Ø110mm", "Ø160mm", "Ø225mm"]
    },
    {
      id: "pipe-pn20",
      category: "pipes",
      section: "CPVC FlowGuard® Pipes",
      tag: "PN20 · S5",
      name: "CPVC Pipe S5 — PN20",
      description: "FlowGuard® CPVC pipes per DIN 8079/8080 & DIN EN ISO 15877-2. Rated PN20 for high-pressure hot & cold water systems.",
      image: "protect/cpvcPipes%20(1).jpg",
      sizes: ["Ø16mm", "Ø20mm", "Ø25mm", "Ø32mm", "Ø40mm", "Ø50mm", "Ø63mm", "Ø75mm", "Ø90mm", "Ø110mm", "Ø160mm"]
    },
    {
      id: "coupler",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Coupler",
      description: "Straight couplers per DIN EN ISO 15877-3 for permanent solvent-cement joints.",
      image: "protect/coupler.jpg",
      sizes: pnSizes([16, 20, 25, 32, 40, 50, 63], 25)
        .concat(pnSizes([75, 90], 20))
        .concat(pnSizes([110, 160], 16))
    },
    {
      id: "elbow-90",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Elbow 90°",
      description: "90° elbows for direction changes in CPVC distribution lines.",
      image: "protect/elobow.jpg",
      sizes: pnSizes([16, 20, 25, 32, 40, 50, 63], 25)
        .concat(pnSizes([75, 90], 20))
        .concat(pnSizes([110, 160], 16))
    },
    {
      id: "elbow-45",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Elbow 45°",
      description: "45° elbows for smooth direction changes with minimal pressure loss.",
      image: "protect/elbow45deg.jpg",
      sizes: pnSizes([16, 20, 25, 32, 40, 50, 63], 25)
        .concat(pnSizes([75, 90], 20))
        .concat(pnSizes([110, 160], 16))
    },
    {
      id: "reducer-elbow",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Reducer Elbow",
      description: "90° reducing elbows for direction changes with diameter transition.",
      image: "protect/reducer_elbow_90deg.jpg",
      sizes: ["25mm x 20mm PN25", "32mm x 25mm PN25"]
    },
    {
      id: "tee-90",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Tee 90°",
      description: "Equal tee fittings for branching distribution lines.",
      image: "protect/t90.jpg",
      sizes: pnSizes([16, 20, 25, 32, 40, 50, 63], 25)
        .concat(pnSizes([75, 90], 20))
        .concat(pnSizes([110, 160], 16))
    },
    {
      id: "reducer-bush",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Reducer Bush",
      description: "Bush reducers for compact diameter transitions between CPVC pipe sizes.",
      image: "protect/reducerbush.jpg",
      sizes: [
        "20mm x 16mm", "25mm x 20mm", "32mm x 20mm", "32mm x 25mm", "40mm x 32mm", "40mm x 25mm", "40mm x 20mm",
        "50mm x 40mm", "50mm x 32mm", "50mm x 25mm", "50mm x 20mm", "63mm x 50mm", "63mm x 40mm", "63mm x 32mm",
        "63mm x 25mm", "63mm x 20mm", "75mm x 63mm", "75mm x 40mm", "90mm x 75mm", "90mm x 63mm",
        "110mm x 90mm", "110mm x 75mm", "110mm x 63mm", "160mm x 110mm", "160mm x 75mm", "160mm x 63mm"
      ]
    },
    {
      id: "reducer-tee",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Reducer Tee",
      description: "Reducing tee fittings for branching to smaller diameter lines.",
      image: "protect/reducer_Teee_90deg.jpg",
      sizes: ["20mm x 20mm x 16mm PN25", "25mm x 25mm x 20mm PN25", "32mm x 32mm x 25mm PN25"]
    },
    {
      id: "long-reducer",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Long Reducer",
      description: "Long reducers for gradual diameter transitions with minimal pressure loss.",
      image: "protect/longreducer.jpg",
      sizes: ["20mm x 16mm PN25", "25mm x 20mm PN25", "32mm x 25mm PN25", "40mm x 32mm PN25", "50mm x 40mm PN25", "63mm x 50mm PN25"]
    },
    {
      id: "clips",
      category: "accessories",
      section: "CPVC FlowGuard® Fittings",
      tag: "Accessory",
      name: "Clips",
      description: "Pipe support clips for secure mounting of CPVC pipe runs.",
      image: "protect/monclip.jpg",
      sizes: ["16mm", "20mm", "25mm", "32mm"]
    },
    {
      id: "step-over-bend",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "Step Over Bend",
      description: "Step-over bends for crossing over obstacles in pipe routing.",
      image: "protect/stepoverbend.jpg",
      sizes: ["16mm PN25", "20mm PN25", "25mm PN25", "25mm PN25 (Short)", "32mm PN25"]
    },
    {
      id: "end-cap",
      category: "fittings",
      section: "CPVC FlowGuard® Fittings",
      tag: "Fitting",
      name: "End Cap",
      description: "End caps for sealing CPVC pipe terminations.",
      image: "protect/endcap.jpg",
      sizes: pnSizes([16, 20, 25, 32, 40, 50, 63], 25)
        .concat(pnSizes([75, 90], 20))
        .concat(pnSizes([110, 160], 16))
    },
    {
      id: "flange",
      category: "flanges",
      section: "Flanges",
      tag: "Flange",
      name: "Flange",
      description: "CPVC flanges for connecting to equipment, valves and pumps.",
      image: "protect/fladge.jpg",
      sizes: ["20mm", "25mm", "32mm", "40mm", "50mm", "63mm", "75mm", "90mm", "110mm", "160mm (Vanstone)"]
    },
    {
      id: "blind-flange",
      category: "flanges",
      section: "Flanges",
      tag: "Flange",
      name: "Blind Flange",
      description: "Blind flanges for sealing pipe ends and equipment connections.",
      image: "protect/blindfladge.jpg",
      sizes: ["75mm", "90mm", "110mm", "160mm"]
    },
    {
      id: "union",
      category: "valves",
      section: "Valves",
      tag: "PN20",
      name: "Union",
      description: "Union fittings (PN20) for assembly and disassembly of pipe runs.",
      image: "protect/union.jpg",
      sizes: ["16mm", "20mm", "25mm", "32mm", "40mm", "50mm", "63mm"]
    },
    {
      id: "double-union-valve",
      category: "valves",
      section: "Valves",
      tag: "PN16",
      name: "Double Union Ball Valve",
      description: "Double union ball valves (PN16) for flow isolation with easy maintenance access.",
      image: "protect/doubleunionvalve.jpg",
      sizes: ["20mm", "25mm", "32mm", "40mm", "50mm", "63mm", "75mm", "90mm", "110mm"]
    },
    {
      id: "cpvc-male-adapter",
      category: "threaded",
      section: "Threaded Adapters",
      tag: "CPVC",
      name: "CPVC Male Threaded Adapter",
      description: "CPVC male threaded adapters for transitioning to threaded connections.",
      image: "protect/brassmalethreaded.jpg",
      sizes: ['20mm x 1/2"', '25mm x 3/4"', '32mm x 1"', '40mm x 1-1/4"', '50mm x 1-1/2"', '63mm x 2"']
    },
    {
      id: "cpvc-female-adapter",
      category: "threaded",
      section: "Threaded Adapters",
      tag: "CPVC",
      name: "CPVC Female Threaded Adapter",
      description: "CPVC female threaded adapters for transitioning to threaded connections.",
      image: "protect/brassfemalethreaded.jpg",
      sizes: ['20mm x 1/2"', '25mm x 3/4"', '32mm x 1"', '40mm x 1-1/4"', '50mm x 1-1/2"', '63mm x 2"']
    },
    {
      id: "brass-male-adapter",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass · PN25",
      name: "Brass Male Threaded Adapter",
      description: "Brass male threaded adapters (PN25) for CPVC to threaded transitions.",
      image: "protect/brassmalethreaded.jpg",
      sizes: ['16mm x 1/2"', '20mm x 1/2"', '25mm x 1/2"', '25mm x 3/4"', '32mm x 1"', '40mm x 1-1/4"', '50mm x 1-1/2"', '63mm x 2"', '75mm x 2-1/2"']
    },
    {
      id: "brass-female-adapter",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass · PN25",
      name: "Brass Female Threaded Adapter",
      description: "Brass female threaded adapters (PN25) for CPVC to threaded transitions.",
      image: "protect/brassfemalethreaded.jpg",
      sizes: ['16mm x 1/2"', '20mm x 1/2"', '25mm x 1/2"', '25mm x 3/4"', '32mm x 3/4"', '32mm x 1"', '40mm x 1-1/4"', '50mm x 1-1/2"', '63mm x 2"']
    },
    {
      id: "brass-male-union",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass",
      name: "Brass Male Union",
      description: "Brass male union fittings for serviceable threaded connections.",
      image: "protect/union.jpg",
      sizes: ['20mm x 1/2"', '25mm x 3/4"', '32mm x 1"', '40mm x 1-1/4"', '50mm x 1-1/2"', '63mm x 2"']
    },
    {
      id: "brass-elbow",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass · PN25",
      name: "Brass Elbow",
      description: "Brass elbows (PN25) with female threaded insert for fixture connections.",
      image: "protect/brassthreadedelbow.jpg",
      sizes: ['16mm x 1/2"', '20mm x 1/2"', '25mm x 1/2"', '25mm x 3/4"', '32mm x 3/4"', '32mm x 1"']
    },
    {
      id: "wall-mount-elbow",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass · PN25",
      name: "Wall Mounting Elbow",
      description: "Wall mounting elbows (PN25) with integrated fixing tabs for surface installation.",
      image: "protect/brassthreadedwallmount.jpg",
      sizes: ['16mm x 1/2"', '20mm x 1/2"', '25mm x 1/2"', '25mm x 3/4"', '32mm x 3/4"', '32mm x 1"']
    },
    {
      id: "female-threaded-tee",
      category: "brass",
      section: "Brass & Threaded",
      tag: "Brass · PN25",
      name: "Female Threaded Tee",
      description: "Female threaded tee fittings (PN25) for branch connections to threaded outlets.",
      image: "protect/t90.jpg",
      sizes: ['16mm x 1/2"', '20mm x 1/2"', '25mm x 1/2"', '25mm x 3/4"', '32mm x 3/4"', '32mm x 1"', '40mm x 3/4"', '50mm x 3/4"', '63mm x 3/4"']
    },
    {
      id: "stop-valve",
      category: "valves",
      section: "Valves",
      tag: "PN25",
      name: "Stop Valve",
      description: "Stop valves (PN25) for flow control and isolation in potable water lines.",
      image: "protect/stopvalve.jpg",
      sizes: pnSizes([16, 20, 25, 32], 25)
    },
    {
      id: "concealed-valve",
      category: "valves",
      section: "Valves",
      tag: "PN25",
      name: "Concealed Valve",
      description: "Concealed valves (PN25) for in-wall installations with chrome escutcheon.",
      image: "protect/concealdvalve.jpg",
      sizes: ["16mm", "20mm", "25mm", "32mm"]
    },
    {
      id: "upvc-plug",
      category: "accessories",
      section: "Accessories",
      tag: "uPVC",
      name: "uPVC Male Plug",
      description: "uPVC male plug with BSP thread for sealing threaded outlets.",
      image: "protect/endcap.jpg",
      sizes: ['1/2" BSP Male Thread']
    },
    {
      id: "solvent-cement",
      category: "accessories",
      section: "Accessories",
      tag: "E-Z Weld",
      name: "CPVC Solvent Cement",
      description: "E-Z Weld 786™ CPVC heavy-body orange cement — medium set, industrial strength. Meets ASTM D2846 & F493. ½ pint (237ml). Part code: SLCEM250.",
      image: "images/ezweld-786-cpvc-cement.png",
      sizes: ["237ml (½ Pint)"]
    }
  ];
})();
