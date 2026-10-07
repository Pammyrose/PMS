# DENR-CAR Performance Monitoring System (PMS)

## System Development Timeline and Schedule Report

Implementing Office / System Owner: Department of Environment and Natural Resources - Cordillera Administrative Region

Prepared by: Pamela Rose Malasan, Computer Programmer I, PMD

Report prepared: 05 October 2026

Reporting coverage: January to December 2026; dated development entries from January to September; continuation schedule for October to December

Document status: Draft for review and confirmation of dates and implementation milestones

Reference: Doc1.docx, containing screenshots of historical timelines and monthly development schedules

The PMS project started in 2026. This report converts the applicable timeline images into editable tables and summarizes the recorded PMS activities for that year. Shaded schedule cells show periods marked in the reference; they do not, by themselves, establish completion or acceptance. All monthly dates in this report refer to 2026.

<!-- pagebreak -->

# 1. Historical System Development Timeline

The chart below reproduces the highlighted activity periods in the applicable 2026 timeline in Doc1.docx. R denotes a period marked in the reference. A dash denotes an unmarked period; it does not prove that no activity occurred. This report covers the project's first development year, January to December 2026.

## 1.1 Development timeline - 2026

| Activity / Month | Jan | Feb | Mar | Apr | May | Jun | Jul | Aug | Sep | Oct | Nov | Dec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Requirements Gathering | R | R | R | R | R | R | R | R | R | - | - | - |
| Analysis and Design | R | R | R | R | R | R | R | R | R | - | - | - |
| Coding / Debugging | R | R | R | R | R | R | R | R | R | - | - | - |
| Internal Testing | - | - | - | R | R | R | R | R | R | - | - | - |
| User Acceptance Testing (UAT) | - | - | - | - | - | - | - | - | - | - | - | - |
| Training of Trainers (ToT) | - | - | - | - | - | - | - | - | - | - | - | - |
| Implementation | - | - | - | - | - | - | - | - | - | - | - | - |

The 2026 chart shows overlapping requirements, design, coding, and internal testing. The later monthly schedules also mark UAT, consultation, hosting, and maintenance windows. Those windows are recorded separately below because the reference images are not fully consistent and contain no dated acceptance or deployment confirmation.

# 2. Development Phases and Schedule Summary

| Phase | Schedule indicated in the reference | Principal activities | Recorded position |
| --- | --- | --- | --- |
| Phase 1: Planning and Analysis | January; continuing planning is also marked in later schedules | Understand monitoring requirements and reporting procedures; coordinate related data requirements. | January narrative records baseline coordination and initiation of PMS work. |
| Phase 2: System Design | January-February for initial database/UI work; database work also shown in March and July onward | Design the database, user interface, application architecture, PAP hierarchy, and office relationships. | Dated entries record January design, March database integration, and July consolidation. |
| Phase 3: Development | March onward; later schedules extend backend/frontend work through the remaining year | Implement dashboards, data entry, role-specific pages, Excel processing, workflows, and reporting. | Dated enhancements are recorded from March to September. |
| Phase 4: Testing and Consultation | Initial testing/documentation shown from April; later schedules mark September-December testing and consultation, and October-December UAT | Verify calculations, workflows, permissions, imports, reports, and usability; address user feedback. | May validation and September testing/consultation have narrative entries. Formal UAT completion is not established. |
| Phase 5: Recalibration and Hosting | Initial schedules mark May-June; later schedules mark October-December | Prepare hosting, recalibrate the system, monitor operations, and resolve issues. | Schedule windows are shown, but actual server deployment and production acceptance dates are not provided. |

Scheduled phases overlap as requirements and user feedback are incorporated during development. Blank accomplishment cells are retained as unconfirmed rather than filled with assumed work.

<!-- pagebreak -->

# 3. Monthly Accomplishments and Development Status

The following entries are based on the dated notes in Doc1.docx. They describe reported work and do not constitute independent verification of completion or acceptance. Dates with no accomplishment narrative are explicitly identified.

## 3.1 January to June

| Period | Recorded PMS activity | Output / development status |
| --- | --- | --- |
| January 12-15 | Participated in a Universe Baseline workshop to understand procedures relevant to the next project. | Preparatory coordination recorded; related-system activity rather than a completed PMS module. |
| January 19-22 | Prepared a Universe Baseline database diagram and initiated the physical and financial performance monitoring project. | PMS initiation and related database planning recorded. |
| January 26-29 | Started the PMS database and prepared the initial user-interface design. | Initial database and UI work recorded. |
| February | Database, UI design, and architecture work are marked in the schedule. | No separate dated February accomplishment narrative supplied. |
| March 2-5 | Developed dashboard data for overall progress, physical targets, total accomplishments, and rankings. | Dashboard functionality recorded. |
| March 9-12 | Integrated the database with the Universe Baseline system for PPA details. | Database integration recorded. |
| March 16-19 | Created cards for targets, accomplishments, and pending values; added PPA assignment controls for indicators, indicator types, and offices; highlighted PENROs and added Select All controls. | Performance cards and assignment-interface enhancements recorded. |
| March 23-26 | Added controls for targets, accomplishments, months, remarks, and summaries; aligned inputs with their PPA details. | Table controls and record alignment recorded. |
| March 30-31 | Set quarterly inputs as the default view. | Default period-view adjustment recorded. |
| April 1-30 | Added indicator-type icons and highlighted main activities. | Table readability enhancements recorded. |
| April 13-16 | Aligned remarks per office and added summaries showing the current quarter, month, and annual information. | Remarks and summary presentation improvements recorded. |
| April 20-23 | Improved field data entry and corrected sorting/filtering for responsive and accurate presentation. | Data-entry and filtering refinements recorded. |
| April 27-30 | A date range is shown without an accomplishment description. | Activity details to be confirmed. |
| May 1-8 | Entered data in GASS and STO to validate target and accomplishment monitoring accuracy and functionality. | Module validation activity recorded. |
| May 11-15 | Implemented a year-based dropdown filter for viewing and managing records by reporting year. | Reporting-year filter recorded. |
| May 18-22 | Added a per-row delete control. | Row-management function recorded. |
| May 25-29 | Split the main Blade file into separate sections to improve readability and maintainability. | Interface code organization recorded. |
| June | Testing/documentation and recalibration/hosting are marked in the first-half schedule. | No separate dated June accomplishment narrative supplied. |

The January 5-8 testing entry for CARPIS and ETAMS was excluded from PMS accomplishments because it describes other systems.

## 3.2 July

| Period | Recorded PMS activity | Output / development status |
| --- | --- | --- |
| July 1-2 | Consolidated physical and financial modules into shared database tables and controllers; standardized validation, office scoping, and data saving across sectors. | Shared performance-data processing recorded. |
| July 6-9 | Standardized Administrator, Regional Office, User, and PENRO sector pages with consistent tables, search tools, column controls, expandable PAP rows, and performance sections. | Role-specific interface consistency recorded. The repeated entry in the reference is listed once. |
| July 13-16 | Expanded Excel preview and import to supported sectors; improved PAP hierarchy detection, office/indicator processing, financial mapping, and Excel row order. | Excel processing enhancements recorded. |
| July 20-23 | Added the then-existing PENRO review workflow for physical and financial accomplishments, including approval/decline actions, review notes, status pages, notification counters, and sidebar badges. | Historical approval and notification workflow recorded; see the current-workflow note in Section 4. |
| July 27-30 | Enhanced annual, quarterly, and to-date summaries and added standardized WFP Excel exports for supported sectors. | Performance summaries and reporting enhancements recorded. |

## 3.3 August

| Period | Recorded PMS activity | Output / development status |
| --- | --- | --- |
| August 3-5 | Developed dedicated PENRO monitoring pages for all 11 program sectors, with physical/financial monitoring and data-entry tools. | PENRO monitoring interfaces recorded. |
| August 11-13 | Implemented the then-existing CENRO-to-PENRO accomplishment approval workflow, including review notes and user notifications. | Historical submission-review enhancements recorded; current reviewer permissions have since changed. |
| August 17-20 | Improved financial input presentation, added Activity History, and expanded centralized Excel import. | Financial interface, history, and import enhancements recorded. |
| August 24-27 | Developed WFP Excel report export using the official DENR-CAR 2026 template with automated mapping. | Excel report generation recorded. The current export format should be checked against the deployed version. |

An additional August backend entry describes the submission-and-approval workflow but provides no date. It is covered by the dated workflow entry above rather than assigned an invented completion date.

## 3.4 September

| Period | Recorded PMS activity | Output / development status |
| --- | --- | --- |
| September 1-3 | Improved notification counts, submission history, and review access for Administrator and Regional Office users. | Notification and review-access refinements recorded. |
| September 7-10 | Strengthened role-based access and office scoping for physical and financial performance records. | Permission and record-scope improvements recorded. |
| September 14-17 | Expanded automated testing for approval workflows, dashboard calculations, summaries, office restrictions, and Excel imports. | Testing coverage enhancements recorded. |
| September 20-24 | Reviewed the system with Sir Drake; applied adjustments to dashboard filters, comparisons, financial utilization, rankings, and data caching. | Consultation-driven dashboard refinements recorded. Dates are reproduced as supplied. |
| September 21 | Conducted a system consultation with Sir Drake to identify needed adjustments to dashboards, monitoring features, and overall functionality. | Consultation recorded; falls within the September 20-24 review period. |
| September 28-30 | Implemented accomplishment-period locking, correction reasons, and a review workflow for late changes. | Locked-period correction controls recorded. |

The September notes also mention PC troubleshooting and technical support. These are supporting duties and are not presented as separate PMS development milestones.

<!-- pagebreak -->

# 4. Milestones and Current Development Position

| Milestone | Reference period | Reported outcome |
| --- | --- | --- |
| Project initiation and initial design | January 19-29 | PMS work initiated; initial database and interface design recorded. |
| Core monitoring interface | March-April | Dashboards, performance cards, PAP/indicator assignment, entry controls, and summaries developed/refined. |
| Monitoring validation and year filtering | May | GASS/STO data validation and reporting-year controls recorded. |
| Shared data handling and sector interfaces | July 1-9 | Shared physical/financial processing and consistent role-specific pages recorded. |
| Excel processing and reporting | July 13-30; August 17-27 | Expanded previews/imports and WFP exports recorded. |
| Approval workflow and notifications | July-August; September 1-3 | Review functions, request tracking, and notifications developed and refined. |
| Access controls and testing | September 7-17 | Office scoping strengthened and automated test coverage expanded. |
| Consultation and dashboard refinement | September 20-24 | User review and dashboard adjustments recorded. |
| Locked-month correction process | September 28-30 | Period locking and reason-backed corrections recorded. |
| Formal UAT, training, and production acceptance | Actual dates not supplied | Scheduled or pending confirmation; no completion/acceptance claim made. |

## Current-workflow note

The July and August notes describe an earlier PENRO approval workflow. In the current project, PENRO, CENRO, and designated office users save current/future accomplishments directly, while corrections to locked months require a reason and Regional Office or Administrator approval. PENRO users cannot approve or decline those requests. The historical entries are retained as development history, while the User Manual and IS Development Report should describe the current behavior.

# 5. Continuation Schedule and Confirmation Items

The later reference schedules show continued development, testing, consultation, and hosting work during the fourth quarter. October-December entries below are scheduled windows, not reported completed activities. The individual images differ in their final shaded month, so exact target dates require confirmation against the approved SOW or project schedule.

| Activity | Reference / target window | Expected output | Status to record |
| --- | --- | --- | --- |
| Continued development and bug fixing | October-December 2026, subject to schedule confirmation | Refined monitoring, permissions, calculations, imports, and reports. | Scheduled continuation; actual progress/date to be confirmed. |
| Testing and documentation | October-December 2026, subject to schedule confirmation | Test results, resolved issues, updated user manual, and development/final reports. | Schedule shown; documents prepared separately; completion to be confirmed. |
| User Acceptance Testing | October-December 2026 in the detailed schedules | Recorded user test results, issues, and acceptance decision. | Scheduled window only; actual UAT dates and acceptance not supplied. |
| Consultation | Continued fourth-quarter window in the detailed schedules | Agreed revisions and follow-up actions. | September consultation recorded; later sessions to be confirmed. |
| Server deployment / hosting | October-December 2026 in the detailed schedules | Operational hosting environment and deployment verification. | Scheduled window only; actual deployment date not supplied. |
| System recalibration and maintenance | October-December 2026 in the detailed schedules | Configuration adjustments and operational issue monitoring. | Scheduled window only; actual dates to be confirmed. |
| Training of Trainers / end-user orientation | No period marked in the historical timeline | Training materials, participant records, and data-entry guidance. | Date and delivery status to be confirmed. |
| Implementation and handover | No period marked in the historical timeline | Approved release, responsible operating personnel, and handover record. | Date and acceptance status to be confirmed. |

## Items to confirm before final submission

- Reconcile the differing shaded activity windows in the historical and monthly charts.
- Supply the missing February, April 27-30, and June accomplishment details if available.
- Record actual UAT, training, deployment, implementation, and acceptance dates from supporting records.
- Retain historical workflow descriptions, while using current reviewer permissions in operational documentation.

Prepared by: Pamela Rose Malasan

Position / Office: Computer Programmer I, PMD

Signature / Date: ______________________________

Reviewed / Noted by: [Insert name, position, and office]

Signature / Date: ______________________________
