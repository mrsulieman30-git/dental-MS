/**
 * Quick phrases for dental clinical notes.
 * Categorized common phrases that can be clicked to insert at cursor position.
 */
export const quickPhraseCategories = [
  {
    name: 'Anesthesia',
    icon: 'pi-bolt',
    phrases: [
      '2% Lidocaine 1:100,000 epi, 1 carpule administered via infiltration.',
      '2% Lidocaine 1:100,000 epi, 2 carpules administered via IAN block.',
      '4% Articaine 1:100,000 epi, 1 carpule buccal infiltration.',
      '4% Articaine 1:200,000 epi, 1.5 carpules administered.',
      '3% Mepivacaine (no vasoconstrictor), 1 carpule infiltration.',
      '0.5% Bupivacaine 1:200,000 epi, long-acting block administered.',
      'Topical anesthetic (20% Benzocaine) applied prior to injection.',
      'Patient tolerated anesthesia well with no adverse reactions.',
      'Profound anesthesia achieved. Patient comfortable throughout procedure.',
    ]
  },
  {
    name: 'Exam Findings',
    icon: 'pi-search',
    phrases: [
      'Comprehensive oral evaluation performed. All findings documented in chart.',
      'Periodic oral evaluation. No significant changes since last visit.',
      'Limited oral evaluation for specific problem.',
      'Soft tissue within normal limits. No lesions or abnormalities noted.',
      'Oral cancer screening performed. No suspicious findings.',
      'TMJ examination: no clicking, popping, or deviation on opening.',
      'Occlusion evaluated: Class I. No fremitus or mobility detected.',
      'Heavy calculus deposits noted. OHI provided.',
      'Generalized moderate gingivitis with localized areas of recession.',
      'Patient reports no changes to medical history.',
    ]
  },
  {
    name: 'Restorative',
    icon: 'pi-wrench',
    phrases: [
      'Tooth isolated with rubber dam. Caries excavated completely.',
      'Selective etch technique used. Bonding agent applied and light cured.',
      'Composite resin placed in increments and light cured. Occlusion adjusted.',
      'Final restoration polished. Margins smooth. Occlusion verified.',
      'Amalgam restoration placed. Carved to proper anatomy. Occlusion checked.',
      'Temporary restoration placed with IRM/Cavit. Patient advised.',
      'Crown preparation completed. Final impression taken with PVS.',
      'Temporary crown fabricated and cemented with TempBond.',
      'Permanent crown cemented with RelyX. Occlusion and contacts verified.',
      'Patient tolerated procedure well. Post-op instructions given.',
    ]
  },
  {
    name: 'Endodontics',
    icon: 'pi-cog',
    phrases: [
      'Pulp vitality testing performed. Tooth non-responsive to cold/EPT.',
      'Access opening made. Canals located and working length established.',
      'Canals instrumented to working length using rotary NiTi files.',
      'Canals irrigated with NaOCl and EDTA throughout procedure.',
      'Canals obturated with gutta percha and AH Plus sealer.',
      'Post-operative radiograph confirms adequate obturation.',
      'Calcium hydroxide placed as inter-appointment medicament.',
      'Patient advised of possible post-operative discomfort.',
      'Prescribed: Amoxicillin 500mg TID x 7 days, Ibuprofen 600mg Q6H PRN.',
    ]
  },
  {
    name: 'Periodontics',
    icon: 'pi-chart-line',
    phrases: [
      'Full mouth periodontal charting completed.',
      'Scaling and root planing performed with ultrasonic and hand instruments.',
      'SRP completed in quadrants. Tissue response to be evaluated at re-eval.',
      'Localized irrigation with CHX performed.',
      'Arestin (minocycline) placed in pockets ≥5mm.',
      'Periodontal maintenance completed. Next recall in 3 months.',
      'OHI reinforced. Modified Bass technique demonstrated.',
      'Patient counseled on smoking cessation for periodontal health.',
    ]
  },
  {
    name: 'Oral Surgery',
    icon: 'pi-times-circle',
    phrases: [
      'Tooth luxated and delivered with forceps. Socket curetted.',
      'Surgical extraction performed. Flap elevated, bone removed as needed.',
      'Hemostasis achieved. Socket packed with gelfoam/collagen plug.',
      'Sutures placed (3-0 silk/chromic gut). Post-op instructions given.',
      'Dry socket irrigation and Alvogyl dressing placed.',
      'Post-extraction instructions reviewed. Written instructions provided.',
      'Patient advised: soft diet, no smoking, no straws for 48 hours.',
    ]
  },
  {
    name: 'Post-Op Instructions',
    icon: 'pi-info-circle',
    phrases: [
      'Post-operative instructions reviewed with patient verbally and in writing.',
      'Patient advised to take OTC pain medication as needed.',
      'Avoid hot foods/beverages until anesthesia wears off.',
      'Bite on gauze for 30 minutes. Change as needed.',
      'Ice pack 20 min on/20 min off for first 24 hours.',
      'Follow up appointment scheduled. Call office with any concerns.',
      'Emergency contact information provided.',
    ]
  },
  {
    name: 'Prosthetics',
    icon: 'pi-box',
    phrases: [
      'Denture adjustment performed. Pressure areas relieved.',
      'Final impressions taken for complete/partial denture.',
      'Denture try-in completed. Patient approved esthetics and phonetics.',
      'Denture delivered. Insertion adjustments made. OHI for denture care given.',
      'Implant impression taken with open/closed tray technique.',
      'Implant abutment placed. Torqued to manufacturer specifications.',
      'Implant crown cemented/screw-retained. Occlusion verified.',
    ]
  },
];

export default quickPhraseCategories;
