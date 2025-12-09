# Analisador de Cobertura de Testes - PNSL-NTM
# Gera relatório detalhado da cobertura atual e identifica prioridades

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Análise de Cobertura de Testes" -ForegroundColor Cyan
Write-Host "   PNSL-NTM" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se o arquivo coverage.xml existe
if (-not (Test-Path "coverage.xml")) {
    Write-Host "Arquivo coverage.xml não encontrado!" -ForegroundColor Red
    Write-Host "Execute primeiro: php artisan test --coverage --min=0" -ForegroundColor Yellow
    exit 1
}

# Ler o arquivo XML
[xml]$coverage = Get-Content "coverage.xml"

# Função para calcular cobertura de um arquivo
function Get-FileCoverage {
    param($file)
    
    $metrics = $file.metrics
    if ($metrics.statements -eq 0) {
        return 100
    }
    return [math]::Round(($metrics.coveredstatements / $metrics.statements) * 100, 2)
}

# Função para obter nome curto do arquivo
function Get-ShortName {
    param($fullPath)
    
    $parts = $fullPath -split "\\"
    return $parts[-1] -replace "\.php$", ""
}

# Coletar dados de cobertura
$coverageData = @()

foreach ($package in $coverage.coverage.project.package) {
    foreach ($file in $package.file) {
        $fullPath = $file.name
        $shortName = Get-ShortName $fullPath
        $metrics = $file.metrics
        
        # Determinar categoria
        $category = "Other"
        if ($fullPath -match "\\Controllers\\") { $category = "Controller" }
        elseif ($fullPath -match "\\Models\\") { $category = "Model" }
        elseif ($fullPath -match "\\Services\\") { $category = "Service" }
        elseif ($fullPath -match "\\Commands\\") { $category = "Command" }
        elseif ($fullPath -match "\\Requests\\") { $category = "Request" }
        
        $coverage = Get-FileCoverage $file
        
        $coverageData += [PSCustomObject]@{
            Name              = $shortName
            Category          = $category
            Coverage          = $coverage
            Statements        = [int]$metrics.statements
            CoveredStatements = [int]$metrics.coveredstatements
            Methods           = [int]$metrics.methods
            CoveredMethods    = [int]$metrics.coveredmethods
            FullPath          = $fullPath
        }
    }
}

# Calcular estatísticas gerais
$totalStatements = ($coverageData | Measure-Object -Property Statements -Sum).Sum
$totalCovered = ($coverageData | Measure-Object -Property CoveredStatements -Sum).Sum
$overallCoverage = if ($totalStatements -gt 0) { [math]::Round(($totalCovered / $totalStatements) * 100, 2) } else { 0 }

Write-Host "RESUMO GERAL" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host "Cobertura Total: $overallCoverage%" -ForegroundColor $(if ($overallCoverage -ge 80) { "Green" } elseif ($overallCoverage -ge 60) { "Yellow" } else { "Red" })
Write-Host "Total de Statements: $totalStatements"
Write-Host "Statements Cobertos: $totalCovered"
Write-Host ""

# Estatísticas por categoria
Write-Host "COBERTURA POR CATEGORIA" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Gray

$categories = $coverageData | Group-Object Category | Sort-Object Name
foreach ($cat in $categories) {
    $catStatements = ($cat.Group | Measure-Object -Property Statements -Sum).Sum
    $catCovered = ($cat.Group | Measure-Object -Property CoveredStatements -Sum).Sum
    $catCoverage = if ($catStatements -gt 0) { [math]::Round(($catCovered / $catStatements) * 100, 2) } else { 0 }
    
    $color = if ($catCoverage -ge 80) { "Green" } elseif ($catCoverage -ge 60) { "Yellow" } else { "Red" }
    Write-Host "$($cat.Name): " -NoNewline
    Write-Host "$catCoverage%" -ForegroundColor $color -NoNewline
    Write-Host " ($catCovered/$catStatements statements)"
}

Write-Host ""

# Arquivos com cobertura proxima a 80 porcento (70-85)
Write-Host "ARQUIVOS PROXIMOS A 80 PORCENTO (70-85)" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Gray
$nearTarget = $coverageData | Where-Object { $_.Coverage -ge 70 -and $_.Coverage -lt 85 } | Sort-Object Coverage -Descending
if ($nearTarget.Count -gt 0) {
    foreach ($item in $nearTarget) {
        $missing = $item.Statements - $item.CoveredStatements
        Write-Host "  $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor White
        Write-Host "$($item.Coverage)%" -ForegroundColor Yellow -NoNewline
        Write-Host " - faltam $missing statements"
    }
}
else {
    Write-Host "  Nenhum arquivo nesta faixa" -ForegroundColor Gray
}

Write-Host ""

# Arquivos com cobertura abaixo de 50 porcento
Write-Host "ARQUIVOS COM BAIXA COBERTURA (menor que 50 porcento)" -ForegroundColor Red
Write-Host "======================================" -ForegroundColor Gray
$lowCoverage = $coverageData | Where-Object { $_.Coverage -lt 50 -and $_.Statements -gt 10 } | Sort-Object Statements -Descending | Select-Object -First 10
if ($lowCoverage.Count -gt 0) {
    foreach ($item in $lowCoverage) {
        $missing = $item.Statements - $item.CoveredStatements
        Write-Host "  $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor White
        Write-Host "$($item.Coverage)%" -ForegroundColor Red -NoNewline
        Write-Host " - faltam $missing statements"
    }
}
else {
    Write-Host "  Nenhum arquivo nesta faixa" -ForegroundColor Gray
}

Write-Host ""

# Arquivos sem cobertura
Write-Host "ARQUIVOS SEM COBERTURA (0 porcento)" -ForegroundColor Red
Write-Host "======================================" -ForegroundColor Gray
$noCoverage = $coverageData | Where-Object { $_.Coverage -eq 0 -and $_.Statements -gt 0 } | Sort-Object Statements -Descending
if ($noCoverage.Count -gt 0) {
    foreach ($item in $noCoverage) {
        Write-Host "  $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor White
        Write-Host "$($item.Statements) statements não cobertos" -ForegroundColor Red
    }
}
else {
    Write-Host "  Nenhum arquivo sem cobertura!" -ForegroundColor Green
}

Write-Host ""

# Plano de ação
Write-Host "PLANO DE AÇÃO RECOMENDADO" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Gray

$gap = 80 - $overallCoverage
if ($gap -gt 0) {
    Write-Host ""
    Write-Host "Meta: Aumentar cobertura de $overallCoverage% para 80%" -ForegroundColor Yellow
    Write-Host "Gap: $($gap.ToString('0.00'))%" -ForegroundColor Yellow
    Write-Host ""
    
    Write-Host "PRIORIDADE 1: Completar arquivos próximos a 80%" -ForegroundColor Cyan
    $priority1 = $coverageData | Where-Object { $_.Coverage -ge 70 -and $_.Coverage -lt 80 } | Sort-Object Coverage -Descending | Select-Object -First 5
    if ($priority1.Count -gt 0) {
        foreach ($item in $priority1) {
            $missing = $item.Statements - $item.CoveredStatements
            Write-Host "  - $($item.Name): adicionar ~$missing testes" -ForegroundColor White
        }
    }
    else {
        Write-Host "  Nenhum arquivo nesta categoria" -ForegroundColor Gray
    }
    
    Write-Host ""
    Write-Host "PRIORIDADE 2: Melhorar arquivos com cobertura media (50-70 porcento)" -ForegroundColor Cyan
    $priority2 = $coverageData | Where-Object { $_.Coverage -ge 50 -and $_.Coverage -lt 70 } | Sort-Object @{Expression = { $_.Statements - $_.CoveredStatements }; Descending = $true } | Select-Object -First 5
    if ($priority2.Count -gt 0) {
        foreach ($item in $priority2) {
            $missing = $item.Statements - $item.CoveredStatements
            Write-Host "  - $($item.Name): adicionar ~$missing testes" -ForegroundColor White
        }
    }
    else {
        Write-Host "  Nenhum arquivo nesta categoria" -ForegroundColor Gray
    }
    
    Write-Host ""
    Write-Host "PRIORIDADE 3: Criar testes para arquivos sem cobertura" -ForegroundColor Cyan
    $priority3 = $coverageData | Where-Object { $_.Coverage -eq 0 } | Sort-Object Statements -Descending | Select-Object -First 5
    if ($priority3.Count -gt 0) {
        foreach ($item in $priority3) {
            Write-Host "  - $($item.Name): criar suite de testes completa" -ForegroundColor White
        }
    }
    else {
        Write-Host "  Nenhum arquivo sem cobertura!" -ForegroundColor Green
    }
}
else {
    Write-Host "META ATINGIDA! Cobertura está em $overallCoverage%" -ForegroundColor Green
    Write-Host ""
    Write-Host "Próximos passos:" -ForegroundColor Cyan
    Write-Host "  - Manter cobertura acima de 80%" -ForegroundColor White
    Write-Host "  - Adicionar testes para novas features" -ForegroundColor White
    Write-Host "  - Revisar e melhorar testes existentes" -ForegroundColor White
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host ""

# Salvar relatório em arquivo
$reportPath = "coverage-report-$(Get-Date -Format 'yyyy-MM-dd-HHmmss').txt"
$coverageData | Sort-Object Coverage | Format-Table Name, Category, Coverage, Statements, CoveredStatements -AutoSize | Out-File $reportPath
Write-Host "Relatorio detalhado salvo em: $reportPath" -ForegroundColor Gray
Write-Host ""
