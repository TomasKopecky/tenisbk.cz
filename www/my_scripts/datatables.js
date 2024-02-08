 //inicializace tabulky s potřebnými parametry řazení   
 $(document).ready(function(){
    var table = $('#table').DataTable(
            {
     columnDefs: [
       { type: 'numeric-comma', targets: [5,6] }, // nahradí dle výše uvedeného kódu znak ':' mezerou ve sloupci 4 a 5, aby řadil jen podle vyhraných setů a vyhraných gamů
       { orderable: false, targets: [0] } //vypnutí řazení na prvním sloupci, kde je vygenerováno pořadové číslo
     ],
     "order":[ [ 7, 'desc' ], [ 3, 'desc' ], [ 4, 'asc' ], [ 2, 'desc' ], [ 5, 'desc' ], [ 6, 'desc'] ],//řazení podle sloupců 6 (úspěšnost), 2 (výhry), 4 (jen vyhrané sety) a 5 (jen vyhrané gamy)
  dom: 'Blfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
  } );
  //vypíše index u každého sloupce - vždy dle zobrazených výsledků - dynamická tvorba, mění se dle filtrace
  table.on( 'order.dt search.dt', function () {
        table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
  } );
  } );
  table.draw(); //vykreslí tabulku
  } );