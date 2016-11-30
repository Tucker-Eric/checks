<?php

namespace CheckWriter;

class Writer
{
    protected $pdf;

    protected $page_width = 8.5;

    protected $page_height = 11;

    protected $left_margin = 0;

    protected $top_margin = 0;

    protected $cell_margin = ['top' => .25, 'right' => .25, 'bottom' => .25, 'left' => .25];

    // only used for making page breaks, no position calculations
    protected $columns = 1;

    protected $gutter = 3 / 16;

    protected $rows = 1;

    protected $logo_offset = 0;

    protected $logo_width = 0.33;

    protected $check;

    protected $x;

    protected $y;

    public function __construct()
    {
        $this->pdf = new FPDF('P', 'in', [$this->page_width, $this->page_height]);
        $this->pdf->AddFont('Twcen');
        $this->pdf->AddFont('Micr');
        $this->pdf->AddFont('Courier');
        $this->pdf->SetMargins($this->left_margin, $this->top_margin);
        $this->pdf->SetDisplayMode("fullpage", "continuous");
        $this->pdf->AddPage();
    }

    protected function cellMargin($pos)
    {
        return $this->cell_margin[$pos];
    }

    public function printCheck(Check $check)
    {
        $this->check = $check;
        $this->createBlankCheck()->fillCheck();
    }

    public function printCheckNumber()
    {
        $this->pdf->SetFont('Twcen', '', 11);
        $this->pdf->SetXY($this->x + 5.25, $this->y + 0.33);
        $this->pdf->Cell(1, (11 / 72), $this->check->check_number, 0, 'R');

        return $this;
    }

    public function addLogo()
    {
        if ($this->check->logo !== null) {
            // logo should be: 0.71" x 0.29"
            $this->logo_offset = $this->logo_width + 0.005;  // width of logo
            $this->pdf->Image(
                $this->check->logo,
                $this->x + $this->cellMargin('left'),
                $this->y + $this->cellMargin('top') + .12,
                $this->logo_width
            );
        }

        return $this;
    }

    public function printFromName()
    {
        $this->pdf->SetFont('Twcen', '', 7);

        // name
        $this->pdf->SetXY(
            $this->x + $this->cellMargin('left') + $this->logo_offset,
            $this->y + $this->cellMargin('top') + .1);

        $this->pdf->SetFont('Twcen', '', 10);
        $this->pdf->Cell(2, (10 / 72), $this->check->from_name, 0, 2);
        $this->pdf->SetFont('Twcen', '', 7);
        $this->pdf->Cell(2, (7 / 72), $this->check->from_address1, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->from_address2, 0, 2);

        return $this;
    }

    public function printDate()
    {
        $this->pdf->Line($this->x + 3.5, $this->y + .58, $this->x + 3.5 + 1.2, $this->y + .58);
        $this->pdf->SetXY($this->x + 3.5, $this->y + .48);
        $this->pdf->Cell(1, (7 / 72), 'Date');

        return $this;
    }

    public function printPayee()
    {
        $this->pdf->Line(
            $this->x + $this->cellMargin('left'),
            $this->y + 1.1,
            $this->x + $this->cellMargin('left') + 4.1,
            $this->y + 1.1
        );
        $this->pdf->SetXY($this->x + $this->cellMargin('left'), $this->y + .88);
        $this->pdf->MultiCell(.6, (7 / 72), 'Pay to the order of', 0, 'L');

        return $this;
    }

    public function printAmountBox()
    {
        // amount box
        $this->pdf->Rect($this->x + 4.5, $this->y + .83, 1.1, .25);

        // dollars
        $this->pdf->Line(
            $this->x + $this->cellMargin('left'),
            $this->y + 1.5,
            $this->x + $this->cellMargin('left') + 5.37, $this->y + 1.5
        );

        $this->pdf->SetXY($this->x + $this->cellMargin('left') + 4.37, $this->y + 1.4);

        $this->pdf->Cell(1, (7 / 72), 'Dollars', '', '', 'R');

        return $this;
    }

    public function printBankInfo()
    {
        // bank info
        $this->pdf->SetXY($this->x + $this->cellMargin('left'), $this->y + 1.6);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_1, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_2, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_3, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_4, 0, 2);

        return $this;
    }

    public function printMemoLine()
    {
        $this->pdf->Line(
            $this->x + $this->cellMargin('left'),
            $this->y + 2.225,
            $this->x + $this->cellMargin('left') + 2.9,
            $this->y + 2.225
        );

        $this->pdf->SetXY($this->x + $this->cellMargin('left'), $this->y + 2.125);
        $this->pdf->Cell(1, (7 / 72), 'Memo');

        return $this;
    }

    public function printSignatureLine()
    {
        $this->pdf->Line($this->x + 3.25, $this->y + 2.225, $this->x + 3.25 + 2.375, $this->y + 2.225);

        return $this;
    }

    public function createBlankCheck()
    {
        return $this->printCheckNumber()
            ->addLogo()
            ->printFromName()
            ->printDate()
            ->printPayee()
            ->printAmountBox()
            ->printBankInfo()
            ->printMemoLine();
    }

    public function fillCheck()
    {
        return $this->writeDate()
            ->writePayee()
            ->writeAmount()
            ->writeMemo()
            ->writeAccountInfo()
            ->writeSignature();
    }

    public function writeDate()
    {
        if ($this->check->date !== null) {
            $this->pdf->SetFont('Courier', '', 11);
            $this->pdf->SetXY($this->x + 3.5 + .3, $this->y + .38);
            $this->pdf->Cell(1, .25, $this->check->date);
        }

        return $this;
    }

    public function writePayee()
    {
        if ($this->check->pay_to !== null) {
            $this->pdf->SetFont('Courier', '', 11);
            $this->pdf->SetXY($this->x + $this->cellMargin('left') + .5, $this->y + .88);
            $this->pdf->Cell(1, .25, $this->check->pay_to);
        }

        return $this;
    }

    public function writeAmount()
    {
        if ($this->check->amount > 0) {
            $dollars = (int)$this->check->amount;
            $cents = round(($this->check->amount - $dollars) * 100);
            if ($cents === 0) {
                $cents = '00';
            }
            //$dollars_str = TextualNumber::GetText($dollars);
            $numtxt = new TextualNumber($dollars);
            $dollars_str = $numtxt->numToWords($dollars);

            $amt_string = '***'.ucfirst(strtolower($dollars_str))." dollars and $cents/100***";

            $this->pdf->SetFont('Courier', '', 9);
            $this->pdf->SetXY($this->x + $this->cellMargin('left'), $this->y + 1.28);
            $this->pdf->Cell(1, .25, $amt_string);

            $this->pdf->SetXY($this->x + 4.5 + .06, $this->y + .83);
            $this->pdf->Cell(1, .25, '$'.number_format($this->check->amount, 2));
        }

        return $this;
    }

    public function writeMemo()
    {
        if ($this->check->memo !== null) {
            $this->pdf->SetFont('Courier', '', 8);
            $this->pdf->SetXY($this->x + $this->cellMargin('left') + 0.3, $this->y + 2.02);
            $this->pdf->Cell(1, .25, $this->check->memo);
        }

        return $this;
    }

    public function writeAccountInfo()
    {
        $this->pdf->SetFont('Micr', '', 10);
        $routingstring = "t".$this->check->routing_number."t".$this->check->account_number."o".$this->check->check_number;
        if ($this->check->codeline !== null) {
            $routingstring = $this->check->codeline;
        }
        $this->pdf->SetXY($this->x + $this->cellMargin('left'), $this->y + 2.47);
        $this->pdf->Cell(5, (10 / 72), $routingstring);

        return $this;
    }

    public function writeSignature()
    {
        if ($this->check->signature !== null) {
            if (strtolower(substr($this->check->signature, - 3)) === 'png') {
                $sig_offset = 1.75;  // width of signature
                $this->pdf->Image(
                    $this->check->signature,
                    $this->x + $this->cellMargin('left') + 3.4,
                    $this->y + 1.88, $sig_offset
                );
            } else {
                $this->pdf->SetFont('Arial', 'i', 10);
                $this->pdf->SetXY($this->x + $this->cellMargin('left') + 3.4, $this->y + 2.01);
                $this->pdf->Cell(1, .25, $this->check->signature);

            }
        }

        return $this;
    }

    public function writePreauthDisclaimer()
    {
        if ($this->check->pre_auth !== null) {
            $this->pdf->SetFont('Arial', '', 6);
            $this->pdf->SetXY($this->x + $this->cellMargin('left') + 3.3, $this->y + 2.155);
            $this->pdf->Cell(1, .25, "This check is pre-authorized by your depositor");
        }
    }

    /**
     * @param $checks
     * @return string
     */
    public function printChecks($checks)
    {

        ////////////////////////////
        // label-specific variables
        $label_height = 2.85;
        $label_width = 6;


        $lpos = 0;
        foreach ($checks as $check) {

            $pos = $lpos % ($this->rows * $this->columns);

            // calculate coordinates of top-left corner of current cell
            //    margin        cell offset
            $this->x = $this->cellMargin('left') + (($pos % $this->columns) * ($label_width + $this->gutter));
            //    margin        cell offset
            $this->y = $this->cellMargin('left') + (floor($pos / $this->columns) * $label_height);
            // Print the check
            $this->printCheck($check);
            // Create another page if needed
            if ($pos === (($this->rows * $this->columns) - 1) && ! ($lpos === count($checks) - 1)) {
                $this->pdf->AddPage();
            }

            $lpos ++;
        }

        return $this->pdf->Output();
    }
}
