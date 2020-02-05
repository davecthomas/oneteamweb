<?php include('globals.php'); 
include_once('utilsbase.php');
$title= " Subscription Agreement" ; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="keywords" content="1TeamWeb Team Management Sports Membership Clubs Site"/>
<meta name="description" content="1 Team Web: Focus On Your Team"/>
<title><?php echo $title . " to " . appname;?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
<link rel="icon" type="image/png" href="/1team/img/1teamweb-logo-200.png" />
</head>
<body>
<div id="wrapper"> 
<?php 
//include('nav-notloggedin.php'); 
$bError = false;
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid_login = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid_login);
if (! isValidSession($session )){
	redirectToLogin();
}
// Only admins can execute this script
if (!isUser($session, Role_TeamAdmin)) redirect("login.php?e=".RC_NotAuthorized);

$teamid = $session["teamid"];

?>
<h3><?php echo appname . " " . $title?></h3>

<div class="helpboxtext">
<p class="strong">You must read and accept the agreement at the bottom of the page to use this service.</p>
</div>
<div class="indented-group-noborder">
<p><span ><?php echo appname_nowhitespace . " "?>
(&quot;<?php echo appname_nowhitespace . " "?>&quot;) has developed certain technology, as further described
below, to provide a service that provides a workspace and semantic web portal
for the management of teams. The company identified during registration
(&quot;Customer&quot;) desires to subscribe to at least one edition of the <?php echo appname_nowhitespace . " "?>
Service (as defined below) and <?php echo appname_nowhitespace . " "?> desires to provide access to the <?php echo appname_nowhitespace . " "?>
system and provide the <?php echo appname_nowhitespace . " "?> Service on the terms and conditions set forth
herein.</span></p>

<p 
><span ><?php echo appname_nowhitespace . " "?>
is willing to provide access to the service and documentation described below
to Customer only on the condition that Customer accepts all of the terms in
this Subscription Agreement (&quot;Agreement&quot;). You hereby agree that you
enter into this Agreement on behalf of Customer and that you have the requisite
authority to bind Customer to the terms and conditions of this Agreement.</span></p>

<p><span >By clicking on the
&quot;Accept&quot; button below, Customer acknowledges that it has read this
Agreement, understands it, and agrees to be bound by it. If Customer does not
agree to any of the terms below, <?php echo appname_nowhitespace . " "?> is unwilling to provide access to the
<?php echo appname_nowhitespace . " "?> Service to Customer, and you should click on the &quot;Do Not
Accept&quot; button below to discontinue the registration process.</span></p>

<p 
><b><span >TERMS
AND CONDITIONS</span></b></p>

<p 
><b><span >1.
Definitions</span></b><span >. As used in this
Agreement:</span></p>

<p 
><b><span >1.1
&quot;Customer Data&quot;</span></b><span >&nbsp;</span><span
>means any electronic data or information contained in
any database, template or other similar document submitted by Customer through
the <?php echo appname_nowhitespace . " "?> Service.</span></p>

<p 
><b><span >1.2
&quot;Documentation&quot;</span></b><span >&nbsp;</span><span
>means the user manuals, handbooks, online materials,
specifications or forms provided by <?php echo appname_nowhitespace . " "?> that describe the features,
functionality or operation of the <?php echo appname_nowhitespace . " "?> System</span></p>

<p 
><b><span >1.3
&quot;Fees&quot;</span></b><span >&nbsp;</span><span
>means any applicable fees paid by Customer prior to
accessing the <?php echo appname_nowhitespace . " "?> Service. So long as Customer has no more than <?php echo betauserlimittxt . "(" . betauserlimit . ")"?>&nbsp;
Users of the <?php echo appname_nowhitespace . " "?> Beta Edition service, there shall be no Fees.</span></p>

<p 
><b><span >1.4
&quot;Order Form&quot;</span></b><span >&nbsp;</span><span
>means an online form completed by Customer through <?php echo appname_nowhitespace . " "?>'s
online ordering utility, which, if applicable specifies certain terms related
to Customer's access to the <?php echo appname_nowhitespace . " "?> Service, including Fees, number of Users,
Term and the Subscription Administrator.</span></p>

<p 
><b><span >1.5
&quot;<?php echo appname_nowhitespace . " "?> Service&quot;</span></b><span >&nbsp;</span><span
>means the applicable online service delivered by <?php echo appname_nowhitespace . " "?>
to Customer using the <?php echo appname_nowhitespace . " "?> System. Depending on Customer's choice of <?php echo appname_nowhitespace . " "?>'s
service offerings, <?php echo appname_nowhitespace . " "?> Service refers to either the free <?php echo appname_nowhitespace . " "?>
Beta Edition service or other services as <?php echo appname_nowhitespace . " "?> may make available to
Customer for additional Fees from time to time.</span></p>

<p 
><b><span >1.6
&quot;<?php echo appname_nowhitespace . " "?> System&quot;</span></b><span >&nbsp;</span><span
>means the technology, including hardware and software,
used by <?php echo appname_nowhitespace . " "?> to deliver the <?php echo appname_nowhitespace . " "?> Service to Customer in accordance with
this Agreement.</span></p>

<p 
><b><span >1.7
&quot;Subscription Administrator&quot;</span></b><span >&nbsp;</span><span
>means the individual assigned by Customer having
responsibility for all administrative and billing matters relating to
Customer's use of the <?php echo appname_nowhitespace . " "?> Service, as identified during the online
user provisioning process.</span></p>

<p 
><b><span >1.8
&quot;Term&quot;</span></b><span >&nbsp;</span><span
>means the duration of this Agreement.</span></p>

<p 
><b><span >1.9
&quot;User ID&quot;</span></b><span >&nbsp;</span><span
>means the unique identification name and password
assigned to each User for access to the <?php echo appname_nowhitespace . " "?> Service.</span></p>

<p 
><b><span >1.10
&quot;Users&quot;</span></b><span >&nbsp;</span><span
>means Customer's employees, representatives,
consultants, contractors or agents who are authorized to use the <?php echo appname_nowhitespace . " "?>
Service on behalf of Customer.</span></p>

<p 
><b><span >2.
<?php echo appname_nowhitespace . " "?> SERVICE.</span></b></p>

<p 
><b><span >2.1</span></b><span
> Subscription to <?php echo appname_nowhitespace . " "?> Service. Subject to Customer's
compliance with the terms of this Agreement, <?php echo appname_nowhitespace . " "?> is making its <?php echo appname_nowhitespace . " "?>
Beta Edition service available to Customer for up to <?php echo betauserlimittxt . "(" . betauserlimit . ")"?>&nbsp;Users at no
charge. As a result of the <?php echo appname_nowhitespace . " "?> Beta Edition service being offered at
no charge, <?php echo appname_nowhitespace . " "?> hereby reserves the right to modify, cancel or suspend its
offering of the <?php echo appname_nowhitespace . " "?> Beta Edition at any time, in its sole discretion.
Customer shall have no expectation of the continuation or availability of the <?php echo appname_nowhitespace . " "?>
Beta Edition service.</span></p>

<p 
><b><span >2.2
Subscription to the <?php echo appname_nowhitespace . " "?> Service.</span></b><span >&nbsp;</span><span
>Subject to the terms of this Agreement, <?php echo appname_nowhitespace . " "?> hereby
grants to Customer a non-sublicensable, non-transferable, non-exclusive
subscription to access and use the <?php echo appname_nowhitespace . " "?> Service by: (i) up to <?php echo betauserlimittxt . "(" . betauserlimit . ")"?>&nbsp;
Users in the event that Customer is accessing the <?php echo appname_nowhitespace . " "?> Beta Edition or
(ii) the number of Users for which Customer has paid the applicable Customer
Fee in the event that Customer submits an Order Form for additional , in
accordance with the Documentation and solely for Customer's internal business
purposes of managing its software design, development, maintenance and support.</span></p>

<p 
><b><span >2.3
Additional Users.</span></b><span >&nbsp;</span><span
>Access to the <?php echo appname_nowhitespace . " "?> Service cannot be shared with
anyone other than a User. If Customer wishes to add additional Users beyond the
ten included Users in <?php echo appname_nowhitespace . " "?> Beta Edition (&quot;Additional Users&quot;)
or if Customer wishes to order additional services from <?php echo appname_nowhitespace . " "?>
(&quot;Additional Services&quot;, Customer's Subscription Administrator must
submit a new executed Order Form (&quot;New Order Form&quot;). Upon <?php echo appname_nowhitespace . " "?>'s
approval of the terms of a New Order Form, <?php echo appname_nowhitespace . " "?> shall make the <?php echo appname_nowhitespace . " "?>
Service available to the Additional Users or make the Additional Services
available to Customer on the terms and conditions set forth in this Agreement
and the approved New Order Form. With respect to Additional Users: (i) the term
will be coterminous with the preexisting subscription term (either initial term
or renewal term); and (ii) Customer will be responsible for any additional Fees
for the Additional Users in full for the month in which the New Order Form is
approved by <?php echo appname_nowhitespace . " "?>.</span></p>

<p 
><b><span >2.4
Service Levels.</span></b><span >&nbsp;</span><span
>Subject to the terms of this Agreement, <?php echo appname_nowhitespace . " "?> shall
use commercially reasonable efforts to: (a) maintain the security of the <?php echo appname_nowhitespace . " "?>
Service; (b) provide regular (once monthly) backups for the Customer Data; and
(c) make the <?php echo appname_nowhitespace . " "?> Service generally available 24/7 (24 hours a day, 7 days
a week), except for: (i) planned downtime, which shall be any period outside of
the hours of 9 am to 5 pm, Central Time, Monday through Friday and 12 pm to 5
pm Central Time, Saturday, Sunday and Holidays, for which <?php echo appname_nowhitespace . " "?> uses
commercially reasonable efforts to give eight (8) hours or more notice that the
<?php echo appname_nowhitespace . " "?> Service will be unavailable; and (ii) downtime caused by circumstances
beyond <?php echo appname_nowhitespace . " "?>'s reasonable control, including without limitation, acts of
God, acts of government, flood, fire, earthquakes, civil unrest, acts of
terror, strikes or other labor problems, telecommunications or network failures
or delays, computer failures involving hardware or software, power failures of any cause, and acts of
vandalism (including network intrusions and denial of service attacks. Customer
is solely responsible for providing, at its own expense, all network access to
the <?php echo appname_nowhitespace . " "?> Service, including, without limitation, acquiring, installing and
maintaining all telecommunications equipment, hardware, software and other
equipment as may be necessary to connect to, access and use the <?php echo appname_nowhitespace . " "?>
Service. </span></p>

<p 
><b><span >2.5
Support.</span></b><span >&nbsp;</span><span
>Support to Customers using the free <?php echo appname_nowhitespace . " "?> Beta
Edition services shall be limited to <?php echo appname_nowhitespace . " "?>'s Beta Edition online resources
which <?php echo appname_nowhitespace . " "?> may modify or terminate in its sole discretion from time to
time. Support services provided by <?php echo appname_nowhitespace . " "?> and available for additional Fees
in connection with the <?php echo appname_nowhitespace . " "?> Service. <?php echo appname_nowhitespace . " "?> reserves the right to modify
the support services in its reasonable discretion from time to time.</span></p>

<p 
><b><span >2.6
Security.</span></b><span >&nbsp;</span><span
><?php echo appname_nowhitespace . " "?> has implemented Security Measures (as
hereinafter defined) and maintains the <?php echo appname_nowhitespace . " "?> Service at its hosting facilities.
&quot;Security Measures&quot; means reasonable technical, physical and
procedural controls to protect Customer Data against destruction, loss,
alteration, unauthorized disclosure to third parties or unauthorized access by
employees or contractors employed by <?php echo appname_nowhitespace . " "?>, whether by accident or
otherwise. However, Customer acknowledges and agrees that, notwithstanding such
Security Measures, use of or connection to the Internet provides the
opportunity for unauthorized third parties to circumvent such precautions and
illegally gain access to the Platform Services and Customer Data. Accordingly, <?php echo appname_nowhitespace . " "?>
cannot and does not guarantee the privacy, security or authenticity of any
information so transmitted over or stored in any system connected to the
Internet.</span></p>

<p 
><b><span >2.7
Excess Data Storage Fees.</span></b><span >&nbsp;</span><span
>The maximum disk storage space provided with <?php echo appname_nowhitespace . " "?>
Beta Edition at no additional charge is 100 MB. If the amount of disk
storage required exceeds these limits, you will be charged the then-current
storage fees. <?php echo appname_nowhitespace . " "?> will use reasonable efforts to notify you when the
average storage used per license reaches approximately 90% of the maximum;
however, any failure by <?php echo appname_nowhitespace . " "?> to so notify you shall not affect your
responsibility for such additional storage charges. <?php echo appname_nowhitespace . " "?> reserves the right
to establish or modify its general practices and limits relating to storage of
Customer Data.</span></p>

<p 
><b><span >3.
CUSTOMER'S USE OF THE 1TEAMWEB SERVICE.</span></b></p>

<p 
><b><span >3.1
Access and Security Guidelines.</span></b><span >&nbsp;</span><span
>Customer's Team Administrator will be authorized to
provision up to (i) <?php echo betauserlimittxt . "(" . betauserlimit . ")"?>&nbsp;User IDs if Customer is using the <?php echo appname_nowhitespace . " "?>
Beta Edition service or (ii) that number of User IDs corresponding to the
number of Users for which Customer has paid the applicable Fees. Customer shall
be responsible for ensuring the security and confidentiality of its User ID.
User IDs may be shared within Customer's organization, provided that User IDs
may not be provided to any individual who is not a User (other than the
Subscription Administrator) and each User ID may be assigned to and used by
only one individual User. Customer will use commercially reasonable efforts to
prevent unauthorized access to, or use of, the <?php echo appname_nowhitespace . " "?> Service, and will
notify <?php echo appname_nowhitespace . " "?> promptly of any such unauthorized use. Customer will not use
its access to the <?php echo appname_nowhitespace . " "?> Service to: (a) access or copy any data or
information of other users without their consent; (b) harvest, collect, gather
or assemble information or data regarding other users without their consent;
(c) knowingly interfere with or disrupt the integrity or performance of the <?php echo appname_nowhitespace . " "?>
Service or the data contained therein; or (d) harass or interfere with another
user's use and enjoyment of the <?php echo appname_nowhitespace . " "?> Service. Customer will, at all times,
comply with all applicable local, state, federal, and foreign laws in its use
of the <?php echo appname_nowhitespace . " "?> Service.</span></p>

<p 
><b><span >3.2
Customer Data.</span></b><span >&nbsp;</span><span
>Customer is solely responsible for the Customer Data
and will not provide, post or transmit any Customer Data or any other
information, data or material that: (a) infringes or violates any intellectual
property rights, publicity/privacy rights, law or regulation; or (b) contains
any viruses or programming routines intended to damage, surreptitiously
intercept or expropriate any system, data or personal information. <?php echo appname_nowhitespace . " "?> may
take remedial action if Customer Data violates this Section 3.2; however, <?php echo appname_nowhitespace . " "?>
is under no obligation to review Customer Data for accuracy or potential
liability.</span></p>

<p 
><b><span >3.3
Use Restrictions.</span></b><span >&nbsp;</span><span
>Customer is responsible for all activities that occur
under Customer's User accounts. Customer will not, and will not attempt to: (a)
reverse engineer, disassemble or decompile any component of the <?php echo appname_nowhitespace . " "?>
System; (b) interfere in any manner with the operation of the <?php echo appname_nowhitespace . " "?> Service
or the <?php echo appname_nowhitespace . " "?> System; (c) allow a third party to access the <?php echo appname_nowhitespace . " "?> Service
or transfer to a third party any of Customer's rights under this Agreement,
except as otherwise provided in this Agreement, or otherwise use the <?php echo appname_nowhitespace . " "?>
Service for the benefit of a third party or to operate a service bureau; (d)
copy, modify or make derivative works based on any part of the <?php echo appname_nowhitespace . " "?> System;
(e) create Internet &quot;links&quot; to or from the <?php echo appname_nowhitespace . " "?> Service, or
&quot;frame&quot; or &quot;mirror&quot; any of <?php echo appname_nowhitespace . " "?>'s content which forms
part of the <?php echo appname_nowhitespace . " "?> Service (other than on Customer's own internal intranets);
or (f) otherwise use the <?php echo appname_nowhitespace . " "?> Service in any manner that exceeds the scope
of use permitted under Section 2.2 hereof.</span></p>

<p 
><b><span >3.4
Suspension of Termination of Service</span></b><span >. <?php echo appname_nowhitespace . " "?>
reserves the right to suspend or terminate Customer's Service if in <?php echo appname_nowhitespace . " "?>'s
discretion, Customer has violated any of the terms of this Agreement including,
but not limited to this Section 3. Further <?php echo appname_nowhitespace . " "?> reserves the right to
suspend Customer's service in the event that Customer has not accessed the <?php echo appname_nowhitespace . " "?>
Service for a period of 2 two (2) months. <?php echo appname_nowhitespace . " "?> assumes no liability in
connection with the termination or suspension of service.</span></p>

<p 
><b><span >4.
FEES, PAYMENT.</span></b></p>

<p 
><span >In the
event that Customer orders Additional Users or Additional Services beyond those
included in the <?php echo appname_nowhitespace . " "?> Beta Edition service, as consideration for the
subscription to the <?php echo appname_nowhitespace . " "?> Service provided by <?php echo appname_nowhitespace . " "?> under this Agreement,
Customer will pay <?php echo appname_nowhitespace . " "?> the Fees set forth in the applicable Order Form. All
applicable Fees will be billed on an annual basis and are due at the time of service commencement and at the service annivesary date
for follow-on year renewals of Service, unless stated otherwise in the Order Form.
Overdue amounts shall accrue interest at the rate of 1 % per month, or at the
highest legal interest rate, if less. All Fees owed by Customer in connection
with this Agreement are exclusive of, and Customer shall pay, all sales, use,
excise and other taxes that may be levied upon Customer in connection with this
Agreement, or other transactions contemplated under this Agreement, except for
employment taxes and taxes based on <?php echo appname_nowhitespace . " "?>'s net income. <?php echo appname_nowhitespace . " "?> reserves
the right (in addition to any other rights or remedies <?php echo appname_nowhitespace . " "?> may have
including but not limited to those in Section 3, above) to discontinue the <?php echo appname_nowhitespace . " "?>
Service and suspend all User IDs and Customer's access to the <?php echo appname_nowhitespace . " "?> Service
if any Fees set forth in an Order Form are more than thirty (30) days overdue,
until such amounts are paid in full. Customer shall ensure that its
Subscription Administrator maintains complete, accurate and up-to-date Customer
billing and contact information via the online account section of the <?php echo appname_nowhitespace . " "?>
Service at all times. <?php echo appname_nowhitespace . " "?> will have the right to audit Customer's records
relating to Customer's use of the <?php echo appname_nowhitespace . " "?> Service to verify that Customer has
complied with the terms of this Agreement. If the audit reveals that Customer
has underpaid the amounts owed to <?php echo appname_nowhitespace . " "?> by five percent (5%) or more in any
quarter, Customer will reimburse <?php echo appname_nowhitespace . " "?> for all reasonable costs and expenses
incurred by <?php echo appname_nowhitespace . " "?> in connection with such audit. Customer will promptly pay
to <?php echo appname_nowhitespace . " "?> any amounts shown by any such audit to be owing plus interest as
provided in above.</span></p>

<p 
><b><span >5.
OWNERSHIP.</span></b></p>

<p 
><b><span >5.1 <?php echo appname_nowhitespace . " "?>
System and Technology.</span></b><span >&nbsp;</span><span
>Customer acknowledges that <?php echo appname_nowhitespace . " "?> retains all right,
title and interest in and to the <?php echo appname_nowhitespace . " "?> System and all software, materials,
formats, interfaces, information, data, content and <?php echo appname_nowhitespace . " "?> proprietary
information and technology used by <?php echo appname_nowhitespace . " "?> or provided to Customer in
connection with the <?php echo appname_nowhitespace . " "?> Service and any suggestions, ideas, enhancement
requests, feedback, recommendations or other information provided by Customer
or learned as a result of Customer's use of the Service (the &quot;<?php echo appname_nowhitespace . " "?>
Technology&quot;), and that the <?php echo appname_nowhitespace . " "?> Technology is protected by
intellectual property rights owned by or licensed to <?php echo appname_nowhitespace . " "?>. Other than as
expressly set forth in this Agreement, no license or other rights in the <?php echo appname_nowhitespace . " "?>
Technology are granted to the Customer, and all such rights are hereby
expressly reserved by <?php echo appname_nowhitespace . " "?>.</span></p>

<p 
><b><span >6.
TERM AND TERMINATION.</span></b></p>

<p 
><span >6.1 Term.
Term will begin on the Effective Date and continue as long as the <?php echo appname_nowhitespace . " "?>
Beta Edition is made available by <?php echo appname_nowhitespace . " "?> or for the period of time
specified in an Order Form. In the event that Customer purchases Additional
Users or Additional Services, upon expiration of the initial Term, thereafter,
this Agreement will automatically renew for successive Terms, unless one of the
parties gives written notice of non-renewal at least thirty (30) days prior to
the expiration of the then-current Term. <?php echo appname_nowhitespace . " "?> reserves the right to
increase the Fees applicable to any renewal term upon prior written notice to
Customer.</span></p>

<p 
><b><span >6.2
Early Termination.</span></b><span >&nbsp;</span><span
>Either party may terminate this Agreement upon written
notice if the other party materially breaches the Agreement and does not cure
such breach (if curable) within thirty (30) days after written notice of such
breach. Upon the termination of this Agreement for any reason: (a) any amounts
owed to <?php echo appname_nowhitespace . " "?> under this Agreement before such termination will become
immediately due and payable; and (b) each party will return to the other all
property of the other party in its possession or control. <?php echo appname_nowhitespace . " "?> agrees that
upon any early termination of this Agreement, <?php echo appname_nowhitespace . " "?> will allow the Customer
to access, without the right to modify, enhance or add to, the Customer Data
(either through on-line access or an off-line mechanism provided by <?php echo appname_nowhitespace . " "?>,
at <?php echo appname_nowhitespace . " "?>'s discretion) for a reasonable time period after termination, not to exceed one (1) month.
Thereafter, <?php echo appname_nowhitespace . " "?> will remove all Customer Data from the <?php echo appname_nowhitespace . " "?> System and
all Customer access to or use of the <?php echo appname_nowhitespace . " "?> System and <?php echo appname_nowhitespace . " "?> Service will
be immediately suspended. The rights and duties of the parties under Sections
4, 5, 6.2, 7, 8, 9 and 10 will survive the termination or expiration of this
Agreement.</span></p>

<p 
><b><span >7.
DISCLAIMER.</span></b></p>

<p 
><span ><?php echo appname_nowhitespace . " "?>
makes no warranty concerning the <?php echo appname_nowhitespace . " "?> System or <?php echo appname_nowhitespace . " "?> Service and
Customer acknowledges that <?php echo appname_nowhitespace . " "?>'s sole obligation with regard to the <?php echo appname_nowhitespace . " "?>
Service is to use reasonable efforts to meet the service levels described in
Section 2.3 hereof. ACCORDINGLY, THE 1TEAMWEB SERVICE, THE 1TEAMWEB SYSTEM AND
ALL OTHER DATA, MATERIALS, AND DOCUMENTATION PROVIDED IN CONNECTION WITH THIS
AGREEMENT BY 1TEAMWEB AND ITS SUPPLIERS ARE PROVIDED &quot;AS IS&quot; AND
&quot;AS AVAILABLE,&quot; WITHOUT REPRESENTATIONS OR WARRANTIES OF ANY KIND. 1TEAMWEB
AND ITS SUPPLIERS MAKE NO OTHER WARRANTIES, EXPRESS OR IMPLIED, BY OPERATION OF
LAW OR OTHERWISE, INCLUDING, WITHOUT LIMITATION, ANY IMPLIED WARRANTIES OF
NONINFRINGEMENT, MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE OR ANY
IMPLIED WARRANTIES ARISING OUT OF COURSE OF PERFORMANCE, COURSE OF DEALING OR
USAGE OF TRADE. 1TEAMWEB DOES NOT WARRANT THAT THE 1TEAMWEB SERVICE WILL BE
PROVIDED ERROR-FREE, UNINTERRUPTED, COMPLETELY SECURE OR VIRUS-FREE.</span></p>

<p 
><b><span >8.
INDEMNITY.</span></b></p>

<p 
><b><span >8.1
By Customer.</span></b><span >&nbsp;</span><span
>If any action is instituted by a third party against <?php echo appname_nowhitespace . " "?>:
(a) arising out of or relating to the use of the <?php echo appname_nowhitespace . " "?> System or <?php echo appname_nowhitespace . " "?>
Service (including claims by any customer or business partner of Customer) by
Customer or any third party with Customer's User ID; or (b) alleging that the
Customer Data, or the use of Customer Data pursuant to this Agreement, infringes
the intellectual property or other right of a third party or otherwise causes
harm to a third party, Customer will defend such action at its own expense on
behalf of <?php echo appname_nowhitespace . " "?> and shall pay all damages attributable to such claim which
are finally awarded against <?php echo appname_nowhitespace . " "?> or paid in settlement of such claim. </span></p>

<p 
><b><span >8.2
Conditions.</span></b><span >&nbsp;</span><span
>As a condition of the foregoing indemnification
obligations, the indemnified party will: (a) inform the indemnifying party of a
claim as soon as reasonably practicable after the indemnified party receives
notice of the claim; (b) permit the indemnifying party to assume direction and
control of the defense of the claim (including the right to settle solely for
monetary consideration); and (c) cooperate as requested by the indemnifying
party (at its expense) in the defense of the claim. The indemnified party shall
have the right to participate, at its expense, in the defense of any claim that
is subject to indemnification as set forth in this Section 8.</span></p>

<p 
><b><span >9.
LIMITATION OF LIABILITY.</span></b></p>

<p 
><span >1TEAMWEB'S
TOTAL CUMULATIVE LIABILITY TO CUSTOMER FOR ANY AND ALL CLAIMS ARISNG FROM OR IN
CONNECTION WITH THIS AGREEMENT (UNDER ANY LEGAL THEORY INCLUDING CLAIMS IN
CONTRACT OR TORT), THE 1TEAMWEB SERVICE AND THE 1TEAMWEB SYSTEM, WILL NOT
EXCEED: (A) ONE DOLLAR ($1) IN THE CASE OF CUSTOMERS USING THE FREE 1TEAMWEB
Beta EDITION SERVICE OR (B) IN THE CASE OF CUSTOMERS PURCHASING ADDITIONAL
SERVICES OR ADDITIONAL USERS, TWENTY FIVE PERCENT (25%) OF THE AMOUNTS ACTUALLY
PAID TO 1TEAMWEB BY CUSTOMER IN THE TWELVE (12) MONTH PERIOD IMMEDIATELY
PRECEDING THE CUSTOMER'S FORMAL WRITTEN NOTICE OF THE CLAIM FOR LIABILITY
HEREUNDER. ALL CLAIMS THAT CUSTOMER MAY HAVE AGAINST 1TEAMWEB WILL BE
AGGREGATED TO SATISFY THIS LIMIT AND MULTIPLE CLAIMS WILL NOT ENLARGE THIS
LIMIT. IN NO EVENT WILL 1TEAMWEB BE LIABLE FOR SPECIAL, INCIDENTAL, DIRECT OR
CONSEQUENTIAL DAMAGES ARISING OUT OF OR IN CONNECTION WITH THIS AGREEMENT
(UNDER ANY LEGAL THEORY INCLUDING CLAIMS IN CONTRACT OR TORT), INCLUDING, BUT
NOT LIMITED TO, INTERRUPTED COMMUNICATIONS, LOST DATA OR LOST PROFITS, AND
DAMAGES THAT RESULT FROM INCONVENIENCE, DELAY OR LOSS OF USE OF ANY INFORMATION
OR DATA OR OF THE 1TEAMWEB SYSTEM OR 1TEAMWEB SERVICE, EVEN IF 1TEAMWEB HAS
BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES, AND NOTWITHSTANDING THE
FAILURE OF ESSENTIAL PURPOSE OF ANY LIMITED REMEDY PROVIDED HEREIN.</span></p>

<p 
><b><span >10.
GENERAL PROVISIONS.</span></b></p>

<p 
><b><span >10.1
Publicity.</span></b><span >&nbsp;</span><span
><?php echo appname_nowhitespace . " "?> and Customer may make public announcements,
including but not limited to, press releases and media announcements, of the
existence of this Agreement and the relationship between the parties. All
public announcements by either party concerning this Agreement are subject to
prior written approval by Customer and <?php echo appname_nowhitespace . " "?>, which approval shall not be
unreasonably withheld. The parties will use reasonable efforts to review and
approve public announcements within three (3) days of submittal. Customer
agrees to allow <?php echo appname_nowhitespace . " "?> to use Customer's name in customer lists and other
promotional materials describing Customer as a customer of <?php echo appname_nowhitespace . " "?> and a user
of the <?php echo appname_nowhitespace . " "?> Service.</span></p>

<p 
><b><span >10.2
Modification to Terms.</span></b><span >&nbsp;</span><span
><?php echo appname_nowhitespace . " "?> reserves the right to modify the terms and
conditions of this Agreement or its policies relating to the <?php echo appname_nowhitespace . " "?> System
and the <?php echo appname_nowhitespace . " "?> Service at any time, effective upon posting of an updated
version of this Agreement on the <?php echo appname_nowhitespace . " "?> Service. You are responsible for
regularly reviewing this Agreement. Continued use of the Service after any such
changes shall constitute your consent to such changes.</span></p>

<p 
><b><span >10.3
Assignment.</span></b><span >&nbsp;</span><span
>Customer may not assign or transfer, by operation of
law or otherwise, any of its rights under this Agreement to any third party
without <?php echo appname_nowhitespace . " "?>'s prior written consent. Any attempted assignment or transfer
in violation of the foregoing will be void. <?php echo appname_nowhitespace . " "?> may assign this Agreement
without Customer's consent in connection with a merger, acquisition, corporate
reorganization, or sale of all or substantially all of its assets, and <?php echo appname_nowhitespace . " "?>
may subcontract certain aspects of the <?php echo appname_nowhitespace . " "?> Service to qualified third
parties, provided that any such subcontracting arrangement will not relieve <?php echo appname_nowhitespace . " "?>
of any of its obligations hereunder.</span></p>

<p 
><b><span >10.4
Governing Law and Venue.</span></b><span >&nbsp;</span><span
>This Agreement will be governed by and construed in
accordance with the laws of the State of Texas without giving effect to
principles of conflict of laws. The United Nations Convention on Contracts for
the International Sale of Goods will not apply to this Agreement. Any action or
proceeding arising from or relating to this Agreement must be brought in a
federal or state court sitting in Austin, Texas (provided, however, that
nothing in this Agreement will prevent <?php echo appname_nowhitespace . " "?> from seeking injunctive relief
to enforce the terms of this Agreement in any competent venue or jurisdiction),
and each party irrevocably submits to the jurisdiction and venue of any such
court in any such action or proceeding.</span></p>

<p 
><b><span >10.5
Remedies.</span></b><span >&nbsp;T</span><span
>he parties' rights and remedies under this Agreement
are cumulative. Customer acknowledges that the <?php echo appname_nowhitespace . " "?> System contains
valuable trade secrets and proprietary information of <?php echo appname_nowhitespace . " "?>, that any actual
or threatened breach of Section 3 will constitute immediate, irreparable harm
to <?php echo appname_nowhitespace . " "?> for which monetary damages would be an inadequate remedy, and that
injunctive relief is an appropriate remedy for such breach, and waives any
requirement by <?php echo appname_nowhitespace . " "?> for posting bond. If any legal action is brought to
enforce this Agreement, the prevailing party will be entitled to receive its
attorneys' fees, court costs, and other collection expenses, in addition to any
other relief it may receive.</span></p>

<p 
><b><span >10.6
Notices.</span></b><span >&nbsp;</span><span
>Any notice or other communication required or permitted
under this Agreement and intended to have legal effect must be given in writing
to the other party at the physical and/or email address set forth in the
initial Order Form (each party may change its address from time to time upon
written notice to the other party of the new address). Notices will be deemed
to have been given upon receipt (or when delivery is refused) and may be (a)
delivered personally, (b) sent via certified mail (return receipt requested),
(c) sent via email, cable, telegram, telex, telecopier, fax (all with
confirmation of receipt) or (d) sent by recognized air courier service.</span></p>

<p><b><span >10.7
Severability and Waiver.</span></b><span >&nbsp;</span><span
>In the event that any provision of this Agreement is
held to be invalid or unenforceable, the valid or enforceable portion thereof
and the remaining provisions of this Agreement will remain in full force and
effect. Any waiver or failure to enforce any provision of this Agreement on one
occasion will not be deemed a waiver of any other provision or of such
provision on any other occasion. All waivers must be in writing. Other than as
expressly stated herein, the remedies provided herein are in addition to, and
not exclusive of, any other remedies of a party at law or in equity.</span></p>

<p 
><b><span >10.8
Relationship of the Parties.</span></b><span >&nbsp;</span><span
>The parties to this Agreement are independent
contractors, and no agency, partnership, franchise, joint venture or
employee-employer relationship is intended or created by this Agreement.</span></p>

<p 
><b><span >10.9
Entire Agreement.</span></b><span >&nbsp;</span><span
>This Agreement, together with any related Order Forms
is the entire understanding and agreement of the parties, and supersedes any
and all previous and contemporaneous understandings, agreements, proposals or
representations, written or oral, between the parties, as to the subject matter
hereof.</span></p>

<script type="text/javascript"> 
	function isAccepted(){
	if (document.licenseform.accepted.value == "on"){
		document.licenseform.acceptsubmit.disabled=false;
	} else {
		document.licenseform.acceptsubmit.disabled=true;
	}
}

</script> 
<div class="helpboxtext">
<div style="text-align: center"> 
<form name="licenseform" action="/1team/license.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<h4>Accept the terms by checking this box and pressing the submit button.&nbsp;<input type="checkbox" name="accepted" id="accepted" onchange="isAccepted()"/></h4>
<div id="submitdiv">
<input type="submit" disabled name="acceptsubmit" value="Accept Terms" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</div>
<input type="button" value="Do Not Accept Terms" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href='login-form.php?e=<?php echo RC_NoLicense?>'"/>
<p>Acceptance of terms is required to use the <?php echo appname ?>&nbsp;service.</p>
</form>
</div> 
</div>
</div>
<?php 
// Start footer section
include('footer.php'); ?>
</body>
</html>
