<div id=main>
	<div id=divtoggl>
		<div id=divtogglfilter>
		<select id=togglUsers></select>
		<select id=togglProjects></select>
		<input id=togglDate>
		<button id=togglfilter>Szűrés</button>
		
		</div>
		<div id=divtogglfilterresult></div>
	</div>
	<div id=divmantis>
		Mantis hiba bejelentés
		<div id=divmantisfilter>
            <div class=divmantisitem id=divmantisusers>
                <div class='divitemlabel'>Felelős</div>
                <select id=mantisUsers></select>
            </div>
            <div class=divmantisitem>
                <div class='divitemlabel'>Partner</div>
                <select id=mantisPartners></select>
            </div>
            <div class=divmantisitem id=divmantisreporters>
                <div class='divitemlabel'>Bejelentő</div>
                <select id=mantisReporters></select>
            </div>
			<div class=divmantisitem>
                <div class='divitemlabel'>Időszak</div>
                <select id=mantisMonths></select>
            </div>
            <div class=divmantisitem>
                <div id=divDateNeeded><input type=checkbox id=cbDateNeeded>Felírás dátummal</div>
                <div id=divDurationNeeded><input type=checkbox id=cbDurationNeeded>Ráfordított idő</div>
                <div id=divOnlyTime><input type=checkbox id=cbOnlyTime>Csak idő módosítása</div>
            </div>
		</div>
		<div id=divmantistabs>
			<div class='divmantistab' id=divmantisuj>Új bejelentés írása</div>
			<div class='divmantistab' id=divmantisregi>Meglévő bejelentés folytatása</div>
			<div class=divtabcontent>
			</div>
	</div>
</div>