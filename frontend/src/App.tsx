import { Building2, Check } from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'

function App() {
  return (
    <main className="grid min-h-svh place-items-center bg-background px-5 py-12 text-foreground">
      <Card className="w-full max-w-xl">
        <CardHeader className="gap-4">
          <Building2 className="size-10 text-primary" aria-hidden="true" />
          <Badge variant="secondary">Projeto em preparação</Badge>
          <CardTitle><h1 className="text-3xl font-semibold tracking-tight">Cidade Colaborativa</h1></CardTitle>
          <CardDescription className="text-base">
            A base visual está pronta. As funcionalidades serão definidas na próxima etapa do projeto.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <p className="text-sm text-muted-foreground">Esta página demonstra os componentes instalados.</p>
          <Dialog>
            <DialogTrigger asChild>
              <Button><Check aria-hidden="true" />Testar componente</Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Componentes configurados</DialogTitle>
                <DialogDescription>
                  React, Tailwind CSS, shadcn/ui e Lucide estão integrados. Este diálogo é apenas uma demonstração e não salva dados.
                </DialogDescription>
              </DialogHeader>
            </DialogContent>
          </Dialog>
        </CardContent>
      </Card>
    </main>
  )
}

export default App
